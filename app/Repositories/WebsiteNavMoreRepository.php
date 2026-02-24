<?php

namespace App\Repositories;

use App\Models\WebsiteNavMore;
use App\Models\WebsiteNavMoreLanguage;
use App\Traits\ImageTrait;

class WebsiteNavMoreRepository
{
    use ImageTrait;

    public function all()
    {
        return WebsiteNavMore::with('language')->where('status', '=', '1')->get();
    }

    public function find($id)
    {
        return WebsiteNavMore::find($id);
    }

    public function getByLang($id, $lang)
    {
        if (! $lang) {
            $feature = WebsiteNavMoreLanguage::where('lang', 'en')->where('website_nav_more_id', $id)->first();
        } else {
            $feature = WebsiteNavMoreLanguage::where('lang', $lang)->where('website_nav_more_id', $id)->first();
            if (! $feature) {
                $feature                     = WebsiteNavMoreLanguage::where('lang', 'en')->where('website_nav_more_id', $id)->first();
                $feature['translation_null'] = 'not-found';
            }
        }
        return $feature;
    }

    public function store($request)
    {
        $feature                    = new WebsiteNavMore;
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
        $feature = WebsiteNavMore::findOrfail($id);
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
        return WebsiteNavMore::destroy($id);
    }

    public function status($data)
    {
        $key         = WebsiteNavMore::findOrfail($data['id']);
        
        $key->status = $data['status'];

        return $key->save();
    }

    public function langStore($request, $feature)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteNavMoreLanguage::create([
            'website_nav_more_id'       => $feature->id,
            'lang'                      => isset($request->lang) ? $request->lang : 'en',
            'title'                     => $request->title,
            'description'               => $lines,
        ]);
    }

    public function langUpdate($request)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteNavMoreLanguage::where('id', $request->translate_id)->update([
            'lang'           => $request->lang,
            'title'          => $request->title,
            'description'    => $lines,
        ]);
    }

}
