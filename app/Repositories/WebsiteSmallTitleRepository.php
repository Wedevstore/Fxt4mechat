<?php

namespace App\Repositories;

use App\Models\WebsiteSmallTitle;
use App\Models\WebsiteSmallTitleLanguage;
use App\Traits\ImageTrait;

class WebsiteSmallTitleRepository
{
    use ImageTrait;

    public function all()
    {
        return WebsiteSmallTitle::with('language')->where('status', '=', '1')->take(6)->latest()->get();
    }

    public function find($id)
    {
        return WebsiteSmallTitle::find($id);
    }

    public function getByLang($id, $lang)
    {
        if (! $lang) {
            $feature = WebsiteSmallTitleLanguage::where('lang', 'en')->where('website_small_title_id', $id)->first();
        } else {
            $feature = WebsiteSmallTitleLanguage::where('lang', $lang)->where('website_small_title_id', $id)->first();
            if (! $feature) {
                $feature                     = WebsiteSmallTitleLanguage::where('lang', 'en')->where('website_small_title_id', $id)->first();
                $feature['translation_null'] = 'not-found';
            }
        }
        return $feature;
    }

    public function store($request)
    {
        $feature                    = new WebsiteSmallTitle;
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
        $feature->save();
        $this->langStore($request, $feature);
        return $feature;
    }

    public function update($request, $id)
    {
        $feature = WebsiteSmallTitle::findOrfail($id);
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->title             = $request->title;
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
        return WebsiteSmallTitle::destroy($id);
    }

    public function status($data)
    {
        $key         = WebsiteSmallTitle::findOrfail($data['id']);
        
        $key->status = $data['status'];

        return $key->save();
    }

    public function langStore($request, $feature)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteSmallTitleLanguage::create([
            'website_small_title_id'       => $feature->id,
            'lang'                      => isset($request->lang) ? $request->lang : 'en',
            'title'                     => $request->title,
            'description'               => $lines,
        ]);
    }

    public function langUpdate($request)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteSmallTitleLanguage::where('id', $request->translate_id)->update([
            'lang'           => $request->lang,
            'title'          => $request->title,
        ]);
    }

}
