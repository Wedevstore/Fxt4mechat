<?php

namespace App\Repositories;

use App\Models\UseCase;
use App\Models\WebsiteUseCaseLanguage;
use App\Traits\ImageTrait;

class WebsiteUseCaseRepository
{
    use ImageTrait;

    public function all()
    {
        return UseCase::with('language')->where('status', '=', '1')->get();
    }

    public function find($id)
    {
        return UseCase::find($id);
    }

    public function getByLang($id, $lang)
    {
        if (! $lang) {
            $feature = WebsiteUseCaseLanguage::where('lang', 'en')->where('use_case_id', $id)->first();
        } else {
            $feature = WebsiteUseCaseLanguage::where('lang', $lang)->where('use_case_id', $id)->first();
            if (! $feature) {
                $feature                     = WebsiteUseCaseLanguage::where('lang', 'en')->where('use_case_id', $id)->first();
                $feature['translation_null'] = 'not-found';
            }
        }
        return $feature;
    }

    public function store($request)
    {
        $feature                    = new UseCase;
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
        $feature->link              = $request->link;
        $feature->save();
        $this->langStore($request, $feature);

        return $feature;
    }

    public function update($request, $id)
    {
        $feature = UseCase::findOrfail($id);
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
        $feature->link              = $request->link;
        $feature->save();
        if (arrayCheck('lang', $request) && $request->lang != 'en') {
            $request->name = $feature->name;
        }
        if ($request->translate_id) {
            $request->lang = $request->lang ?: 'en';
            $this->langUpdate($request);
        } else {
            $this->langStore($request, $feature);
        }

        return $feature;
    }

    public function destroy($id): int
    {
        return UseCase::destroy($id);
    }

    public function status($data)
    {
        $key         = UseCase::findOrfail($data['id']);
        
        $key->status = $data['status'];

        return $key->save();
    }

    public function langStore($request, $feature)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteUseCaseLanguage::create([
            'use_case_id'               => $feature->id,
            'lang'                      => isset($request->lang) ? $request->lang : 'en',
            'title'                     => $request->title,
            'description'               => $lines,
        ]);
    }

    public function langUpdate($request)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteUseCaseLanguage::where('id', $request->translate_id)->update([
            'lang'           => $request->lang,
            'title'          => $request->title,
            'description'    => $lines,
        ]);
    }

}
