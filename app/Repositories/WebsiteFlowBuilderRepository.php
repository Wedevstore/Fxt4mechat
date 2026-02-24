<?php

namespace App\Repositories;

use App\Models\WebsiteFlowBuilder;
use App\Models\WebsiteFlowBuilderLanguage;
use App\Traits\ImageTrait;

class WebsiteFlowBuilderRepository
{
    use ImageTrait;

    public function all()
    {
        return WebsiteFlowBuilder::with('language')->where('status', '=', '1')->take(1)->latest()->get();
    }

    public function find($id)
    {
        return WebsiteFlowBuilder::find($id);
    }

    public function getByLang($id, $lang)
    {
        if (! $lang) {
            $feature = WebsiteFlowBuilderLanguage::where('lang', 'en')->where('website_flow_builder_id', $id)->first();
        } else {
            $feature = WebsiteFlowBuilderLanguage::where('lang', $lang)->where('website_flow_builder_id', $id)->first();
            if (! $feature) {
                $feature                     = WebsiteFlowBuilderLanguage::where('lang', 'en')->where('website_flow_builder_id', $id)->first();
                $feature['translation_null'] = 'not-found';
            }
        }
        return $feature;
    }

    public function store($request)
    {
        $feature                    = new WebsiteFlowBuilder;
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
        $feature = WebsiteFlowBuilder::findOrfail($id);
        if (isset($request->image)) {
            $response               = $this->saveImage($request->image);
            $images                 = $response['images'];
            $feature->image         = $images;
        }
        $feature->title             = $request->title;
        $lines                      = explode("\n",$request->description);
        $feature->description       = $lines;
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
        return WebsiteFlowBuilder::destroy($id);
    }

    public function status($data)
    {
        $key         = WebsiteFlowBuilder::findOrfail($data['id']);
        
        $key->status = $data['status'];

        return $key->save();
    }

    public function langStore($request, $feature)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteFlowBuilderLanguage::create([
            'website_flow_builder_id'       => $feature->id,
            'lang'                      => isset($request->lang) ? $request->lang : 'en',
            'title'                     => $request->title,
            'description'               => $lines,
        ]);
    }

    public function langUpdate($request)
    {
        $lines                      = explode("\n",$request->description);
        return WebsiteFlowBuilderLanguage::where('id', $request->translate_id)->update([
            'lang'           => $request->lang,
            'title'          => $request->title,
            'description'    => $lines,
        ]);
    }

}
