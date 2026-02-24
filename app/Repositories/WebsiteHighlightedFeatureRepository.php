<?php

namespace App\Repositories;

use App\Models\WebsiteHighlightedFeature;
use App\Models\WebsiteHighlightedFeatureLanguage;
use App\Traits\ImageTrait;

class WebsiteHighlightedFeatureRepository
{
    use ImageTrait;

    public function all()
    {
        return WebsiteHighlightedFeature::with('language')->where('status', '=', '1')->take(6)->latest()->get();
    }

    public function find($id)
    {
        return WebsiteHighlightedFeature::find($id);
    }

    public function getByLang($id, $lang)
    {
        if (! $lang) {
            $feature = WebsiteHighlightedFeatureLanguage::where('lang', 'en')->where('website_highlighted_feature_id', $id)->first();
        } else {
            $feature = WebsiteHighlightedFeatureLanguage::where('lang', $lang)->where('website_highlighted_feature_id', $id)->first();
            if (! $feature) {
                $feature                     = WebsiteHighlightedFeatureLanguage::where('lang', 'en')->where('website_highlighted_feature_id', $id)->first();
                $feature['translation_null'] = 'not-found';
            }
        }
        return $feature;
    }

    public function store($request)
    {
        $feature                    = new WebsiteHighlightedFeature;
        if (isset($request->logo)) {
            $response               = $this->saveImage($request->logo);
            $images                 = $response['images'];
            $feature->logo         = $images;
        }
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->mini_title        = $request->mini_title;
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
        $feature->link              = $request->link;
        $feature->lable              = $request->lable;
        $feature->save();
        $this->langStore($request, $feature);
        return $feature;
    }

    public function update($request, $id)
    {
        $feature = WebsiteHighlightedFeature::findOrfail($id);
        if (isset($request->logo)) {
            $response               = $this->saveImage($request->logo);
            $images                 = $response['images'];
            $feature->logo         = $images;
        }
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->mini_title        = $request->mini_title;
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
        $feature->link              = $request->link;
        $feature->lable              = $request->lable;
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
        return WebsiteHighlightedFeature::destroy($id);
    }

    public function status($data)
    {
        $key         = WebsiteHighlightedFeature::findOrfail($data['id']);
        
        $key->status = $data['status'];

        return $key->save();
    }

    public function langStore($request, $feature)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteHighlightedFeatureLanguage::create([
            'website_highlighted_feature_id'       => $feature->id,
            'lang'                      => isset($request->lang) ? $request->lang : 'en',
            'title'                     => $request->title,
            'description'               => $lines,
            'mini_title'                => $request->mini_title,
            'lable'                     => $request->lable,
        ]);
    }

    public function langUpdate($request)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteHighlightedFeatureLanguage::where('id', $request->translate_id)->update([
            'lang'           => $request->lang,
            'title'          => $request->title,
            'description'    => $lines,
        ]);
    }

}
