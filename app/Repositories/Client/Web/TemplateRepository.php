<?php
namespace App\Repositories\Client\Web;
use App\Enums\TypeEnum;
use App\Models\Template;
use App\Models\WebTemplate;
use App\Traits\ImageTrait;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use App\Traits\RepoResponse;
use App\Traits\TelegramTrait;
use App\Traits\TemplateTrait;
use App\Traits\WhatsAppTrait;
use App\Services\TemplateService;
use App\Services\WebTemplateService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplateRepository
{
    use CommonTrait, ImageTrait, RepoResponse, TelegramTrait, WhatsAppTrait,TemplateTrait;
    const GRAPH_API_BASE_URL = 'https://graph.facebook.com/v19.0/';
    private $model;

    protected $whatsappService;

    public function __construct(WebTemplate $model, WhatsAppService $whatsappService)
    {
        $this->model           = $model;
        $this->whatsappService = $whatsappService;
    }

    public function combo()
    {
        return $this->model
            ->where('client_id', auth()->user()->client_id)
            ->pluck('name', 'id');
    }


    public function all()
    {
        return $this->model->withPermission()->latest()->paginate(setting('pagination'));
    }

    public function allTemplates()
    {
        return $this->model->withPermission()->latest()->get();
    }

    public function activeSegments()
    {
        return $this->model->withPermission()->where('status', 1)->get();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function get_size($file_path)
    {
        return Storage::size($file_path);
    }

    private function uploadMediaToFacebookStep1($fileUrl, $fileSize, $mimeType)
    {
        $accessToken = getClientWhatsAppAccessToken(Auth::user()->client);
        // Upload the image to Facebook
        $appId = getClientWhatsAppID(Auth::user()->client);
        $apiUrl = self::GRAPH_API_BASE_URL . "{$appId}/uploads?file_length={$fileSize}&file_type={$mimeType}&access_token={$accessToken}";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => file_get_contents($fileUrl),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: ' . $mimeType,
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        // Parse the response to extract the session ID
        $responseArray = json_decode($response, true);
        if (isset($responseArray['id'])) {
            return $responseArray['id'];
        } else {
            return null; // Handle error condition
        }
    }

    private function uploadMediaToFacebookStep2($fileUrl, $session_id)
    {
        $accessToken = getClientWhatsAppAccessToken(Auth::user()->client);
        // Upload the image to Facebook
        $apiUrl = self::GRAPH_API_BASE_URL . "{$session_id}";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => file_get_contents($fileUrl), // Assuming $fileUrl contains the file contents
            CURLOPT_HTTPHEADER => array(
                'Authorization: OAuth ' . $accessToken,
                'file_offset: 0',
                'Content-Type: text/plain'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        // Parse the response to extract the session ID
        $responseArray = json_decode($response, true);
        if (isset($responseArray['h'])) {
            return $responseArray['h'];
        } else {
            return null; // Handle error condition
        }
    }

    private function uploadMediaToFacebook($file)
    { 
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $session_id = $this->uploadMediaToFacebookStep1($file, $fileSize, $mimeType);
        if (empty($session_id)) {  
            return null;
        }
          // Handle different MIME types
          if (str_contains($mimeType, 'image')) {
            $response  = $this->saveImage($file);
            $media_url = $response['images'];
            $media_url = getFileLink('original_image', $media_url);
        } elseif (str_contains($mimeType, 'video')) {
            $response  = $this->saveFile($file, 'mp4', false);
            $media_url = asset('public/'.$response);
        } elseif (str_contains($mimeType, 'audio')) {
            $response  = $this->saveFile($file, 'mp3', false);
            $media_url = asset('public/'.$response);
            // $media_url = asset($this->saveFile($file, 'mp3', false));
        } elseif (str_contains($mimeType, 'pdf')) {
            $response  = $this->saveFile($file, 'pdf', false);
            $media_url = asset('public/'.$response);
            // $media_url = asset($this->saveFile($file, 'pdf', false));
        }
        $media = $this->uploadMediaToFacebookStep2($media_url, $session_id);
        if (empty($media)) {
            return null;
        }
        return $media;
    }

    public function store($request)
    {
        try {
            $template_name = Str::lower($request->template_name);
            $message_body = $request->message_body;
            $message_type = $request->message_type;
            $mediaUrl      = null;
            $mimeType      = null;
            $file_name     = null;

            if (!empty($message_type)) {
                if ($message_type == "TEXT") {
                    $message_type = 'text';

                } elseif ($message_type == "IMAGE") {
                    if ($request->hasFile('header_image')) {
                        $response = $this->saveImage($request->file('header_image'));
                        $mediaUrl              = getFileLink('original_image', $response['images']);
                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }
                    }

                    if (!empty($mediaUrl)) {
                        $message_type = 'image';
                        $mimeType = 'image/jpeg';
                    }
                    
                } elseif ($message_type == "VIDEO") {

                    if ($request->hasFile('header_video')) {
                        $file    = $request->file('header_video');
                        $fileExtension         = $file->getClientOriginalExtension();
                        $mediaUrl              = asset('public/'.$this->saveFile($file, $fileExtension, false));

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }
                    }
                    if (!empty($mediaUrl)) {
                        $message_type = 'video';
                        $mimeType = 'video/mp4';
                    }

                } elseif ($message_type == "DOCUMENT") {

                    if ($request->hasFile('header_document')) {
                        $file                     = $request->file('header_document');
                        $fileExtension            = $file->getClientOriginalExtension();

                        $mediaUrl                = asset('public/'.$this->saveFile($request->header_document, $fileExtension, false));

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }
                    }

                    if (!empty($mediaUrl)) {
                        $message_type = 'document';
                        $mimeType     = 'application/pdf';
                        $file_name    = 'file.pdf';
                    }

                } elseif ($message_type == "AUDIO") {
                    
                    if ($request->hasFile('media_url')) {
                        $file    = $request->file('media_url');
                        
                        $fileExtension         = $file->getClientOriginalExtension();
                        
                        $mediaUrl              = asset('public/'.$this->saveWebTemplateFile($file, $fileExtension, false));
                        
                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }
                    }
                    if (!empty($mediaUrl)) {
                        $message_type = 'audio';
                        $mimeType = 'audio/mp4';
                    }

                } elseif ($message_type == "LOCATION") {
                    
                }
            }

            $template = new $this->model();
            $template->client_id = Auth::user()->client->id;
            $template->name = $template_name;
            $template->message_type = $message_type;
            $template->message = $message_body;
            $template->media_url = $mediaUrl;
            $template->file_name = $file_name;
            $template->mimetype = $mimeType;
            $template->status = 1;
            $template->save();

            return $this->formatResponse(true, __('message_template_created_successfully'), route('client.web.templates.index'), []);
        } catch (\Throwable $e) {
            \Log::error($e);
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Store error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), route('client.web.templates.index'), []);
        }
    }

    public function update($request, $id)
    {
        try {
            $template = $this->model->find($id);
            if (!$template) {
                return $this->formatResponse(false, __('template_not_found'), 'client.web.templates.index', []);
            }

            $template_name = Str::lower($request->template_name);
            $message_body  = $request->message_body;
            $message_type  = $request->message_type;

            $mediaUrl   = $template->media_url;
            $mimeType   = $template->mimetype;
            $file_name  = $template->file_name;

            // --- Handle media upload based on message type ---
            if (!empty($message_type)) {
                if ($message_type == "TEXT") {
                    $message_type = 'text';
                    $mediaUrl = null;
                    $mimeType = null;
                    $file_name = null;
                }

                elseif ($message_type == "IMAGE") {
                    if ($request->hasFile('header_image')) {
                        $response = $this->saveImage($request->file('header_image'));
                        $mediaUrl = getFileLink('original_image', $response['images']);

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }

                        $mimeType = 'image/jpeg';
                        $message_type = 'image';
                    }
                }

                elseif ($message_type == "VIDEO") {
                    if ($request->hasFile('header_video')) {
                        $file = $request->file('header_video');
                        $fileExtension = $file->getClientOriginalExtension();

                        $mediaUrl = asset('public/' . $this->saveFile($file, $fileExtension, false));

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }

                        $mimeType = 'video/mp4';
                        $message_type = 'video';
                    }
                }

                elseif ($message_type == "DOCUMENT") {
                    if ($request->hasFile('header_document')) {
                        $file = $request->file('header_document');
                        $fileExtension = $file->getClientOriginalExtension();

                        $mediaUrl = asset('public/' . $this->saveFile($file, $fileExtension, false));

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }

                        $mimeType  = 'application/pdf';
                        $file_name = 'file.pdf';
                        $message_type = 'document';
                    }
                }

                elseif ($message_type == "AUDIO") {
                    if ($request->hasFile('header_audio')) {
                        $file = $request->file('header_audio');
                        $fileExtension = $file->getClientOriginalExtension();

                        $mediaUrl = asset('public/' . $this->saveWebTemplateFile($file, $fileExtension, false));

                        if (empty($mediaUrl)) {
                            return $this->formatResponse(false, __('unable_to_upload_media'), 'client.web.templates.index', []);
                        }

                        $mimeType = 'audio/mp4';
                        $message_type = 'audio';
                    }
                }
            }

            // --- Update template ---
            $template->update([
                'name'         => $template_name,
                'message_type' => $message_type,
                'message'      => $message_body,
                'media_url'    => $mediaUrl,
                'file_name'    => $file_name,
                'mimetype'     => $mimeType,
            ]);

            return $this->formatResponse(true, __('template_updated_successfully'), route('client.web.templates.index'), []);

        } catch (\Throwable $e) {
            \Log::error($e);
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Update error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), route('client.web.templates.index'), []);
        }
    }


    public function destroy($id)
    {
        try {
            $this->model->where('id', $id)->delete();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.web.templates.index', []);
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), 'client.web.templates.index', []);
        }
    }

    public function syncTemplateByID($id)
    {
        try {
            $clientSetting = Auth::user()->client->whatsappSetting;
            $template = $this->model->withPermission()->find($id);
            $accessToken = getClientWhatsAppAccessToken(Auth::user()->client);
            $apiUrl = self::GRAPH_API_BASE_URL . "/{$template->template_id}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $templateObject = json_decode($response);
            if (isset($templateObject) && isset($templateObject->error)) {
                $error_message = isset($templateObject->error->error_user_msg) ?
                    $templateObject->error->error_user_msg :
                    $templateObject->error->message;
                return $this->formatResponse(
                    false,
                    $error_message,
                    'client.templates.index',
                    []
                );
            }
            $template = $this->model->withPermission()->firstOrNew(['template_id' => $templateObject->id]);
            $template->fill([
                'name'          => $templateObject->name,
                'client_setting_id' => $clientSetting->id,
                'components'    => $templateObject->components ?? [],
                'category'      => $templateObject->category,
                'language'      => $templateObject->language,
                'client_id'     => Auth::user()->client->id,
                'status'        => $templateObject->status,
                'type'          => TypeEnum::WHATSAPP,
            ]);
            $template->save();
            return $this->formatResponse(
                true,
                __('template_sync_successfully'),
                'client.templates.index',
                []
            );
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }           
            logError('Error: ', $e);
             return $this->formatResponse(
                false,
                $e->getMessage(),
                'client.templates.index',
                []
            );
        }
    }

    public function getTemplateByID($id)
    {
        $data  = $this->find($id);
        return view('backend.client.web.campaigns.partials.__template', compact('data'));
    }

    public function statusChange($request)
    {
        $id = $request['id'];

        return $this->model->find($id)->update($request);
    }

    public function loadTemplate()
    {
        $clientSetting = Auth::user()->client->whatsappSetting;
        return $this->getLoadTemplate($clientSetting);
    }

    public function whatsappTemplate()
    {
        return $this->model->where('client_id', auth()->user()->client_id)->latest()->paginate();
    }
    public function activeWhatsappTemplate()
    {
        return $this->model->where('client_id', auth()->user()->client_id)->latest()->get();
    }
}
