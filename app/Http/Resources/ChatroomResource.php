<?php

namespace App\Http\Resources;

use App\Enums\MessageStatusEnum;
use App\Models\ContactTag;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatroomResource extends JsonResource
{
    public function toArray($request)
    {
        $last_message   = $this->lastMessage;
        $message        = '';
        if ($last_message) {
            $message = $last_message->value;
            if ($last_message->message_type == 'image' && $last_message->header_image) {
                $message = 'An image was sent';
            } elseif ($last_message->message_type == 'video' && $last_message->header_video) {
                $message = 'A video was sent';
            } elseif ($last_message->message_type == 'audio' && $last_message->header_audio) {
                $message = 'An audio was sent';
            } elseif ($last_message->message_type == 'document' && $last_message->header_document) {
                $message = 'A document was sent';
            }
        }

        $assignee_agent = null;

        if (nullCheck($this->assignee_id)) {
            $assignee_agent = User::where('id', $this->assignee_id)->first();
        }

        $tags           = ContactTag::where('contact_id', $this->id)->with('tag')->orderBy('id', 'DESC')->get();

        return [
            'id'                    => $this->id,
            'receiver_id'           => $this->id,
            'sender_id'             => $this->contact_id,
            'name'                  => $this->name,
            'last_conversation_at'  => $this->last_conversation_at,
            'total_unread_messages' => Message::where('contact_id', $this->id)
                ->where('status', MessageStatusEnum::DELIVERED->value)
                ->where('is_contact_msg', 1)
                ->count(),
            'image'                 => $this->profile_pic,
            'phone'                 => isDemoMode() ? '+*************' : @$this->phone,
            'has_msg'               => (bool) $last_message,
            'assignee_id'           => nullCheck($this->assignee_id),
            'assignee_name'         => $assignee_agent ? $assignee_agent->name : '',
            'type'                  => $this->type,
            'message'               => $last_message ? [
                'title'      => $message,
                'is_seen'    => (bool) $last_message->status == 'read',
                'created_at' => Carbon::parse($last_message->created_at)->diffForHumans(),
            ] : (object) [],
            'tags'                  => TagResource::collection($tags),
        ];
    }
}
