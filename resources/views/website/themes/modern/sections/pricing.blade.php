<!-- Pricing Section Start -->
<section class="pricing__section py-100 bg-color py-sm-60" id="pricing">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section__title-wrapper mb-20 text-center" data-aos="fade-up" data-aos-duration="700">
                    <div class="section__title">
                        <h4 class="subtitle">
                            @php
                                $small_title = $small_titles->skip(4)->first();
                            @endphp

                            @if ($small_title)
                                <span class="icon">
                                    <img src="{{ getFileLink('original_image', $small_title->image) }}" alt="meta" />
                                </span>
                                {{ @$small_title->language->title, app()->getLocale() }}
                            @endif
                        </h4>
                        <h2 class="title">{!! setting('pricing_section_title',app()->getLocale()) !!}</h2>
                        <p class="desc">{!! setting('pricing_section_subtitle',app()->getLocale()) !!}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="pricing__wrapper" data-aos="fade-up" data-aos-duration="800">
                    <div class="custom__tabs v2 text-center">
                        <ul class="nav nav-pills" id="pills-tab" role="tablist">
                            @php($i=0)
                            @foreach($plans2 as $plan=>$value)
                                @if(count($value) >0)
                                    @php($i++)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if($i==1) active @endif" id="{{$plan}}-pricing-tab" data-bs-toggle="tab" data-bs-target="#{{$plan}}-pricing" type="button" role="tab" aria-controls="{{$plan}}-pricing" aria-selected="true">
                                            {{__($plan)}}
                                        </button>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        
                    </div>
                    <!-- Tab Content Start -->
                    <div class="tab-content">
                        @php($i = 0)
                        @foreach($plans2 as $plan => $value)
                            @if(count($value) > 0)
                                @php($i++)
                            @php($default_currency = setting('default_currency'))
                            @php($currency_symbol = get_symbol($default_currency))
                                <div class="tab-pane fade @if($i == 1) active show @endif" id="{{ $plan }}-pricing" role="tabpanel" aria-labelledby="{{ $plan }}-pricing-tab">
                            <div class="pricing__grid">
                                <!-- Pricing Item -->
                                @foreach($value as $key => $plan_feature)
                                    <div class="pricing__item active">
                                        <div class="pricing__header">
                                            <h3 class="title">
                                                {{ $plan_feature->name }}
                                                @if($plan_feature->featured == '1')
                                                    <span class="badges">{{ __('recommended') }}</span>
                                                @endif
                                            </h3>
                                            <p class="desc">{{ $plan_feature->description }}</p>
                                            <div class="pricing__tag">
                                                <span class="price">{{ $currency_symbol.convert_price_without_symbol($plan_feature->price) }}</span>
                                                <sub>/ {{ $default_currency }} {{ $plan_feature->billing_period }}</sub>
                                            </div>
                                        </div>

                                        <ul class="pricing__features">
                                            <li>Contact<span>{{ $plan_feature->contact_limit === -1 ? __('unlimited') : $plan_feature->contact_limit }}</span></li>
                                            <li>Conversation<span>{{ $plan_feature->conversation_limit === -1 ? __('unlimited') : $plan_feature->conversation_limit }}</span></li>
                                            <li>Campaign<span>{{ $plan_feature->campaigns_limit === -1 ? __('unlimited') : $plan_feature->campaigns_limit }}</span></li>
                                            <li>User/Teammate<span>{{ $plan_feature->team_limit === -1 ? __('unlimited') : $plan_feature->team_limit }}</span></li>
                                            <li>{{ __('max_flow_builder') }}<span>{{ $plan_feature->max_flow_builder === -1 ? __('unlimited') : $plan_feature->max_flow_builder }}</span></li>
                                            <li>{{ __('max_bot_reply') }}<span>{{ $plan_feature->max_bot_reply === -1 ? __('unlimited') : $plan_feature->max_bot_reply }}</span></li>
                                            <li>Telegram Access <span>{{ $plan_feature->telegram_access == '1' ? __('yes') : __('no') }}</span></li>
                                            @php($chatWidgetActivated    = addon_is_activated('chat_widget'))
                                            @php($chatWidgetRouteExists  = Route::has('client.chatwidget.index'))
                                            @if($chatWidgetActivated && $chatWidgetRouteExists)
                                                <li>{{ __('chatwidget') }} <span>{{ $plan_feature->max_chatwidget === -1 ? __('unlimited') : $plan_feature->max_chatwidget }}</span></li>
                                            @endif
                                        </ul>

                                        <div class="pricing__btn">
                                            <a class="btn" href="{{ route('client.upgrade.plan', $plan_feature->id) }}">Buy Now</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                                @endforeach
                    </div>
                    <!-- Tab Content Start -->
                    <!-- Pricing Item -->
                    <div class="pricing__item featured mt-30">
                        <div class="pricing__header">
                            <h3 class="title text-primary">
                                Free Plan
                                <span class="badges">Recommended</span>
                            </h3>
                            <p class="desc">Utilize platforms like WhatsApp and Telegram to enhance engagement.</p>
                            <div class="pricing__tag">
                                <span class="price text-black">$0.00</span>
                                <sub>/ USD Per Month</sub>
                            </div>
                        </div>

                        <ul class="pricing__features">
                            <li>Contact Limit<span>100</span></li>
                            <li>Campaign Limit<span>1</span></li>
                            <li>Conversation Limit<span>100</span></li>
                            <li>Telegram Access <span>No</span></li>
                        </ul>

                        <div class="pricing__btn">
                            <a class="btn" href="{{route('register')}}">Start Free</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Pricing Section End -->