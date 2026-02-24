<!-- Start Pricing Area -->
<div class="dd-pricing-area dreamd-section-gap-big" id="pricing">
    <div class="container">
 
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center sal-animate" data-sal="slide-up" data-sal-duration="400" data-sal-delay="150">
                    <h3 class="title mb--20">{!! setting('pricing_section_title',app()->getLocale()) !!}</h3>
                    <p class="description b1">{!! setting('pricing_section_subtitle',app()->getLocale()) !!}</p>
                </div>
                <nav class="spa-tab">
                    <div class="tab-btn-grp nav nav-tabs mb-3 text-center justify-content-center" id="nav-tab-two" role="tablist">
                        @php($i=0)
                        @foreach($plans2 as $plan=>$value)
                            @if(count($value) >0)
                                @php($i++)
                                <button class="nav-link @if($i==1) active @endif" id="{{$plan}}-pricing-tab" data-bs-toggle="tab" data-bs-target="#{{$plan}}-pricing" type="button" role="tab" aria-controls="{{$plan}}-pricing" aria-selected="true">
                                    {{__($plan)}}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </nav>
            </div>
        </div>

        <!-- Content Part -->
        <div class="wrapper">
            <div class="tab-content p-0 bg-transparent border-0 border bg-light" id="nav-tabContent-two">
                @php($i = 0)
                @foreach($plans2 as $plan => $value)
                    @if(count($value) > 0)
                        @php($i++)
                        <div class="tab-pane fade @if($i == 1) active show @endif" id="{{ $plan }}-pricing" role="tabpanel" aria-labelledby="{{ $plan }}-pricing-tab">
                            <div class="row row--15 mt_dec--30">
                                @foreach($value as $key => $plan_feature)
                                    <div class="col-lg-4 col-md-6 col-12 mt--30">
                                        <div class="dd-pricing @if($plan_feature->featured == '1') active @endif">
                                            <div class="pricing-inner">
                                                <div class="pricing-header text-center" style="background-color: {{ $plan_feature->color }};">
                                                    <h5 class="title">{{ $plan_feature->name }}</h5>
                                                    <p class="desc">{{ $plan_feature->description }}</p>
                                                </div>
                                                <div class="pricing-body">
                                                    <div class="pricing text-center">
                                                        <div class="price-wrapper">
                                                            <span class="price">{{ convert_price_without_symbol($plan_feature->price) }}</span>
                                                        </div>
                                                        <div class="subtitle">
                                                            <span>/ {{ setting('default_currency') }}</span>
                                                            <span>{{ $plan_feature->billing_period }}</span>
                                                        </div>
                                                    </div>
                                                    <a class="btn-default btn-border round btn-large d-block" href="{{ route('client.upgrade.plan', $plan_feature->id) }}">{{ __('buy_now') }}</a>
                                                    <ul class="list-style--1">
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('team_limit') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->team_limit === -1 ? __('unlimited') : $plan_feature->team_limit }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('campaign_limit') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->campaigns_limit === -1 ? __('unlimited') : $plan_feature->campaigns_limit }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('contact_limit') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->contact_limit === -1 ? __('unlimited') : $plan_feature->contact_limit }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('conversation_limit') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->conversation_limit === -1 ? __('unlimited') : $plan_feature->conversation_limit }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('telegram_access') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="textt">
                                                                    {{ $plan_feature->telegram_access == '1' ? __('yes') : __('no') }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('max_flow_builder') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->max_flow_builder === -1 ? __('unlimited') : $plan_feature->max_flow_builder }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('max_bot_reply') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="number">
                                                                    {{ $plan_feature->max_bot_reply === -1 ? __('unlimited') : $plan_feature->max_bot_reply }}
                                                                </span>
                                                            </div>
                                                        </li>


                                                        <li>
                                                            <div class="left">
                                                                <i class="las la-check-circle"></i>
                                                                <span class="text">{{ __('featured') }}</span>
                                                            </div>
                                                            <div class="right">
                                                                <span class="textt">
                                                                    {{ $plan_feature->featured == '1' ? __('yes') : __('no') }}
                                                                </span>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>




    </div>
</div>
    
<!-- End Pricing Area -->