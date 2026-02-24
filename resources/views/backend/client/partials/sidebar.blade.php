<header class="navbar-dark-v1">
    <div class="header-position">
        <span class="sidebar-toggler">
            <i class="las la-times"></i>
        </span>
        <div class="dashboard-logo d-flex justify-content-center align-items-center py-20">
            <a class="logo" href="">
                <img src="{{ setting('admin_logo') && @is_file_exists(setting('admin_logo')['original_image']) ? get_media(setting('admin_logo')['original_image']) : get_media('images/default/logo/logo.png') }}"
                    alt="Logo">
            </a>
            <a class="logo-icon" href="">
                <img src="{{ setting('admin_mini_logo') && @is_file_exists(setting('admin_mini_logo')['original_image']) ? get_media(setting('admin_mini_logo')['original_image']) : get_media('images/default/logo/logo-mini.png') }}"
                    alt="Logo">
            </a>
        </div>
        <nav class="side-nav">
            <ul id="accordionSidebar">
                <li class="{{ menuActivation(['client/dashboard'], 'active') }}">
                    <a href="{{ route('client.dashboard') }}" role="button" aria-expanded="false"
                        aria-controls="dashboard">
                        <i class="las la-tachometer-alt"></i>
                        <span>{{ __('dashboard') }}</span>
                    </a>
                </li>
                @can('manage_whatsapp')
                    <li
                        class="{{ menuActivation(['client/contacts-list', 'client/contact/import', 'client/bot-reply*', 'client/whatsapp/overview', 'client/templates', 'client/template/create', 'client/template/edit/*', 'client/segments', 'client/segment/edit/*', 'client/contacts', 'client/contact/create', 'client/contact/edit/*', 'client/chat-widget/list', 'client/chat-widget/create', 'client/chat-widget/edit/*', 'client/chat-widget/view/*', 'client/flow-builders', 'client/flow-builders/*', 'client/whatsapp/embedded-signup', 'client/contact-attributes/*'], 'active') }}">
                        <a href="#manage" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ menuActivation(['client/contacts-list', 'client/contact/import', 'client/whatsapp/overview', 'client/templates', 'client/template/create', 'client/template/edit/*', 'client/segments', 'client/segments-edit/*', 'client/contacts', 'client/contact/create', 'client/contact/edit/*', 'client/chat-widget/list', 'client/chat-widget/create', 'client/chat-widget/edit/*', 'client/chat-widget/view/*', 'client/flow-builders', 'client/flow-builders/*', 'client/whatsapp/embedded-signup', 'client/contact-attributes/*'], 'true', 'false') }}"
                            aria-controls="manageClient">
                            <i class="lab la-whatsapp"></i>
                            <span>{{ __('whatsapp') }}</span>
                        </a>
                        <ul class="sub-menu collapse {{ menuActivation(['client/contacts-list', 'client/contact/import', 'client/bot-reply*', 'client/whatsapp/overview', 'client/templates', 'client/template/create', 'client/template/edit/*', 'client/segments', 'client/segment/edit/*', 'client/contacts', 'client/contact/create', 'client/contact/edit/*', 'client/chat-widget/list', 'client/chat-widget/create', 'client/chat-widget/edit/*', 'client/chat-widget/view/*', 'client/flow-builders', 'client/flow-builders/*', 'client/whatsapp/embedded-signup', 'client/contact-attributes/*'], 'show') }}"
                            id="manage" data-bs-parent="#accordionSidebar">
                            <li>
                                <a class="{{ menuActivation(['client/whatsapp/overview'], 'active') }}"
                                    href="{{ route('client.whatsapp.overview') }}">{{ __('overview') }}</a>
                            </li>
                            <li>
                                <a class="{{ menuActivation(['client/contacts-list', 'client/contacts-list/edit/*'], 'active') }}"
                                    href="{{ route('client.contacts_list.index') }}">{{ __('contact_lists') }}</a>
                            </li>
                            <li>
                                <a class="{{ menuActivation(['client/segments', 'client/segment/edit/*'], 'active') }}"
                                    href="{{ route('client.segments.index') }}">{{ __('segments') }}</a>
                            </li>
                            <li>
                                <a class="{{ menuActivation(['client/contacts', 'client/contact/import', 'client/contact/create', 'client/contact/edit/*', 'client/contact-attributes/*'], 'active') }}"
                                    href="{{ route('client.contacts.index') }}">{{ __('contacts') }}</a>
                            </li>
                           
                            <li>
                                <a class="{{ menuActivation(['client/bot-reply*'], 'active') }}"
                                    href="{{ route('client.bot-reply.index') }}">
                                    {{ __('bot_replies') }}
                                </a>
                            </li>
                            @can('manage_flow')
                                <li>
                                    <a class="{{ menuActivation(['client/flow-builders', 'client/flow-builders/*'], 'active') }}"
                                        href="{{ route('client.flow-builders.index') }}">
                                        <span>{{ __('flow_builder') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('manage_template')
                                <li>
                                    <a class="{{ menuActivation(['client/templates', 'client/template/create', 'client/template/edit/*'], 'active') }}"
                                        href="{{ route('client.templates.index') }}">{{ __('templates') }}</a>
                                </li>
                                @endif
                                @php
                                    $chatWidgetActivated = addon_is_activated('chat_widget');
                                    $chatWidgetRouteExists = Route::has('client.chatwidget.index');
                                @endphp
                                @if ($chatWidgetActivated && $chatWidgetRouteExists && auth()->user()->can('manage_widget'))
                                    <li>
                                        <a class="{{ menuActivation(['client/chat-widget/list', 'client/chat-widget/create', 'client/chat-widget/edit/*', 'client/chat-widget/view/*'], 'active') }}"
                                            href="{{ route('client.chatwidget.index') }}">{{ __('chatwidget') }}
                                            @if (env('DEMO_MODE'))
                                            <div class="badges">Addon</div>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endcan

                    @php
                        $omnisync = addon_is_activated('omnisync');
                    @endphp
                    @if ($omnisync)
                        @if ( !empty(@auth()->user()->client->activeSubscription->messenger_access == '1'))
                            @can('manage_messenger')
                                <li class="{{menuActivation(['client/messenger/','client/messenger/templates',''],'active')}}">
                                    <a href="#messenger" class="dropdown-icon" data-bs-toggle="collapse" role="button" aria-expanded="{{ menuActivation([], 'true', 'false') }}" aria-controls="messenger">
                                        <i class="lab la-facebook-messenger"></i>
                                        <span>{{ __('messenger') }}</span>
                                    </a>
                                    <ul class="sub-menu collapse {{ menuActivation(['client/messenger/*'], 'show') }}" id="messenger" data-bs-parent="#accordionSidebar">
                                        
                                        <li>
                                            <a class="{{ menuActivation(['client/messenger/contacts', 'client/messenger/contact/import', 'client/messenger/contact/create', 'client/messenger/contact/edit/*', 'client/messenger/contact-attributes/*'], 'active') }}" href="{{route('client.messenger.contacts.index')}}">{{ __('contacts') }}</a>
                                        </li>
                                        
                                    </ul>
                                </li>
                            @endcan
                        @endif

                        @if ( !empty(@auth()->user()->client->activeSubscription->instagram_access == '1'))
                            @can('manage_instagram')
                                <li class="{{menuActivation(['client/instagram/','client/instagram/templates',''],'active')}}">
                                    <a href="#instagram" class="dropdown-icon" data-bs-toggle="collapse" role="button" aria-expanded="{{ menuActivation([], 'true', 'false') }}" aria-controls="instagram">
                                        <i class="lab la-instagram"></i>
                                        <span>{{ __('instagram') }}</span>
                                    </a>
                                    <ul class="sub-menu collapse {{ menuActivation(['client/instagram/*'], 'show') }}" id="instagram" data-bs-parent="#accordionSidebar">
                                        
                                        <li>
                                            <a class="{{ menuActivation(['client/instagram/contacts', 'client/instagram/contact/import', 'client/instagram/contact/create', 'client/instagram/contact/edit/*', 'client/instagram/contact-attributes/*'], 'active') }}" href="{{route('client.instagram.contacts.index')}}">{{ __('contacts') }}</a>
                                        </li>
                                        
                                    </ul>
                                </li>
                            @endcan
                        @endif
                    @endif

                    @if ( !empty(@auth()->user()->client->activeSubscription->telegram_access == '1'))
                        @can('manage_telegram')
                            <li
                                class="{{ menuActivation(['client/telegram/overview', 'client/telegram/groups', 'client/telegram/subscribers/list', 'client/telegram/templates'], 'active') }}">
                                <a href="#telegram" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ menuActivation([], 'true', 'false') }}" aria-controls="telegram">
                                    <i class="lab la-telegram"></i>
                                    <span>{{ __('telegram') }}</span>
                                </a>
                                <ul class="sub-menu collapse {{ menuActivation(['client/telegram/overview', 'client/telegram/groups', 'client/telegram/subscribers/list', 'client/telegram/templates'], 'show') }}"
                                    id="telegram" data-bs-parent="#accordionSidebar">
                                    <li>
                                        <a class="{{ menuActivation(['client/telegram/overview'], 'active') }}"
                                            href="{{ route('client.telegram.overview') }}">{{ __('overview') }}</a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/telegram/groups'], 'active') }}"
                                            href="{{ route('client.groups.index') }}">{{ __('groups') }}</a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/telegram/subscribers/list'], 'active') }}"
                                            href="{{ route('client.telegram.subscribers.index') }}">{{ __('subscribers') }}</a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                    @endif

                    @can('manage_campaigns')
                        <li
                            class="{{ menuActivation(['client/whatsapp/campaigns', 'client/whatsapp/campaigns/*/view', 'client/telegram/campaigns', 'client/whatsapp/campaign/*', 'client/telegram/campaign/*','client/telegram/campaigns/*/view'], 'active') }}">
                            <a href="#campaigns" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ menuActivation([], 'true', 'false') }}" aria-controls="campaigns">
                                <i class="las la-bullhorn"></i>
                                <span>{{ __('campaigns') }}</span>
                            </a>
                            <ul class="sub-menu collapse {{ menuActivation(['client/whatsapp/campaigns', 'client/whatsapp/campaigns/*/view', 'client/telegram/campaigns', 'client/whatsapp/campaign/*', 'client/telegram/campaign/*','client/telegram/campaigns/*/view'], 'show') }}"
                                id="campaigns" data-bs-parent="#accordionSidebar">
                                <li>
                                    <a class="{{ menuActivation(['client/whatsapp/campaigns', 'client/whatsapp/campaigns/*/view', 'client/whatsapp/campaign/*'], 'active') }}"
                                        href="{{ route('client.whatsapp.campaigns.index', ['campaign_type' => 'whatsapp']) }}">{{ __('whatsapp') }}</a>
                                </li>
                                @if (!empty(@auth()->user()->client->activeSubscription->telegram_access == '1'))
                                    <li>
                                        <a class="{{ menuActivation(['client/telegram/campaigns', 'client/telegram/campaign/*','client/telegram/campaigns/*/view'], 'active') }}"
                                            href="{{ route('client.telegram.campaigns.index', ['campaign_type' => 'telegram']) }}">{{ __('telegram') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endcan
                     @php
                        $sms_marketing = addon_is_activated('sms_marketing');
                    @endphp
                    @if ($sms_marketing)
                        @can('manage_sms_marketing')
                            <li class="{{ menuActivation(['client/sms/campaign', 'client/sms/campaign/create', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create'], 'active') }}">
                                <a href="#smscampaigns" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ menuActivation(['client/sms/campaign', 'client/sms/campaign/create', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create'], 'true', 'false') }}" 
                                    aria-controls="smscampaigns">
                                    <i class="las la-comment-dots"></i>
                                    <span>{{ __('sms_merketing') }}</span>
                                    @if (env('DEMO_MODE'))
                                    <div class="badges bg-white text-black" style="z-index: 2">Addon</div>
                                    @endif
                                </a>
                                <ul class="sub-menu collapse {{ menuActivation(['client/sms/campaign', 'client/sms/campaign/create', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create'], 'show') }}"
                                    id="smscampaigns" data-bs-parent="#accordionSidebar">
                                    <li>
                                        <a class="{{ menuActivation(['client/sms/campaign', 'client/sms/campaign/create', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*'], 'active') }}"
                                           href="{{ route('client.sms.campaign.index') }}">{{ __('broadcast') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/sms/template'], 'active') }}"
                                           href="{{ route('client.sms.template.index') }}">{{ __('template') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/sms/provider'], 'active') }}"
                                           href="{{ route('client.sms.provider.index') }}">{{ __('provider') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/sms/history/list','client/sms/send-now/create'], 'active') }}"
                                           href="{{ route('client.sms.history.index') }}">{{ __('history_log') }}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan
                    @endif

                    @can('manage_chat')
                        <li class="{{ menuActivation('client/chat', 'active') }}">
                            <a href="{{ route('client.chat.index') }}">
                                <i class="las la-sms"></i>
                                <span>{{ __('inbox') }}</span>
                            </a>
                        </li>
                    @endcan

                    
                    @php
                        $saleBotEcommerce = addon_is_activated('salebot_ecommerce');
                    @endphp
                    @if ($saleBotEcommerce)
                        {{-- @can('salebot_ecommerce') --}}
                            <li class="{{ menuActivation(['client/order/*','client/order*', 'client/sms/campaign/create', 'client/payment-gateway-settings', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create','client/shop-settings*','client/ecommerce/preference'], 'active') }}">
                                <a href="#salebot_ecommerce" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                    aria-expanded="{{ menuActivation(['client/order/','client/order/index', 'client/sms/campaign/create', 'client/payment-gateway-settings', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create','client/shop-settings*','client/ecommerce/preference'], 'true', 'false') }}" 
                                    aria-controls="salebot_ecommerce">
                                    <i class="las la-comment-dots"></i>
                                    <span>{{ __('ecommerce') }}</span>
                                    @if (env('DEMO_MODE'))
                                    <div class="badges bg-white text-black" style="z-index: 2">Addon</div>
                                    @endif
                                </a>
                                <ul class="sub-menu collapse {{ menuActivation(['client/order*','client/order/*', 'client/payment-gateway-settings', 'client/sms/campaign/create', 'client/sms/campaign/edit/*', 'client/sms/campaign/view/*','client/sms/template','client/sms/setting','client/sms/provider','client/sms/history/list','client/sms/send-now/create','client/shop-settings*','client/ecommerce/preference'], 'show') }}"
                                    id="salebot_ecommerce" data-bs-parent="#accordionSidebar">
                                    <li>
                                        <a class="{{ menuActivation(['client/order*'], 'active') }}"
                                           href="{{ route('client.order.index') }}">{{ __('orders') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/payment-gateway-settings'], 'active') }}"
                                           href="{{ route('client.payment.gateway.settings') }}">{{ __('payment_gateway') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/shop-settings/shopify'], 'active') }}"
                                           href="{{ route('client.shop.settings',['shop' => 'shopify']) }}">{{ __('shopify_config') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/shop-settings/bigcommerce'], 'active') }}"
                                           href="{{ route('client.shop.settings',['shop' => 'bigcommerce']) }}">{{ __('bigcommerce_config') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/shop-settings/woocommerce'], 'active') }}"
                                           href="{{ route('client.shop.settings',['shop' => 'woocommerce']) }}">{{ __('woocommerce_config') }}
                                        </a>
                                    </li>
                                    {{-- <li>
                                        <a class="{{ menuActivation(['client/ecommerce/preference'], 'active') }}"
                                           href="{{ route('client.ecommerce.preference') }}">{{ __('preference') }}
                                        </a>
                                    </li> --}}
                                </ul>
                            </li>
                        {{-- @endcan --}}
                    @endif

                    @php
                        $emirosync = addon_is_activated('emirosync');
                    @endphp
                    @if ($emirosync)
                        
                        <li class="{{menuActivation(['client/order/','client/order/index',''],'active')}}">
                            <a href="#emirosync" class="dropdown-icon" data-bs-toggle="collapse" role="button" aria-expanded="{{ menuActivation(['client/order/','client/order/index',''], 'true', 'false') }}" aria-controls="order">
                                <i class="las la-truck"></i>
                                <span>{{ __('order') }}</span>
                            </a>
                            <ul class="sub-menu collapse {{ menuActivation(['client/order', 'client/order/*'], 'show') }}" id="emirosync" data-bs-parent="#accordionSidebar">
                                <li>
                                    <a class="{{ menuActivation(['client/order/index', 'client/order/index'], 'active') }}" href="{{route('client.order.index')}}">{{ __('order') }}</a>
                                </li>
                            </ul>
                        </li>
                        
                    @endif

                    @can('manage_team')
                        <li
                            class="{{ menuActivation(['client/team-list', 'client/team/create', 'client/team/edit/*'], 'active') }}">
                            <a href="{{ route('client.team.index') }}">
                                <i class="las la-user-tie"></i>
                                <span>{{ __('team_member') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('manage_ticket')
                        <li class="{{ menuActivation(['client/tickets', 'client/tickets/*'], 'active') }}">
                            <a href="{{ route('client.tickets.index') }}">
                                <i class="las la-ticket-alt"></i>
                                <span>{{ __('my_ticket') }}</span>
                            </a>

                        </li>
                    @endcan
                    @can('manage_ai_writer')
                        <li class="{{ menuActivation(['client/ai-writer', 'client/ai-writer-setting'], 'active') }}">
                            <a href="#ai" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ menuActivation(['client/ai-writer', 'client/ai-writer-setting'], 'true', 'false') }}"
                                aria-controls="ai">
                                <i class="lab la-rocketchat"></i>
                                <span>{{ __('ai_assistent') }}</span>
                            </a>
                            <ul class="sub-menu collapse {{ menuActivation(['client/ai-writer', 'client/ai-writer-setting'], 'show') }}"
                                id="ai" data-bs-parent="#accordionSidebar">
                                <li>
                                    <a class="{{ menuActivation('client/ai-writer', 'active') }}"
                                        href="{{ route('client.ai.writer') }}">
                                        <span>{{ __('ai_writer') }}</span>
                                    </a>
                                </li>
                                <li><a class="{{ menuActivation('client/ai-writer-setting', 'active') }}"
                                        href="{{ route('client.ai_writer.setting') }}">{{ __('setting') }}</a></li>
                            </ul>
                        </li>
                    @endcan

                    @can('manage_setting')
                        <li
                            class="{{ menuActivation(['client/whatsapp-settings', 'client/rapiwa-settings', 'client/billing/details', 'client/telegram-settings', 'client/general-settings', 'client/messenger-settings', 'client/instagram-settings', 'client/shop-settings', 'client/payment-gateway-settings'], 'active') }}">
                            <a href="#setting" class="dropdown-icon" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ menuActivation(['client/whatsapp-settings', 'client/billing/details', 'client/telegram-settings', 'client/general-settings','client/messenger-settings','client/instagram-settings', 'client/shop-settings', 'client/payment-gateway-settings'], 'true', 'false') }}"
                                aria-controls="setting">
                                <i class="las la-cog"></i>
                                <span>{{ __('setting') }}</span>
                            </a>
                            <ul class="sub-menu collapse {{ menuActivation(['client/whatsapp-settings', 'client/rapiwa-settings', 'client/billing/details', 'client/telegram-settings','client/messenger-settings','client/instagram-settings','client/general-settings', 'client/shop-settings', 'client/payment-gateway-settings'], 'show') }}"
                                id="setting" data-bs-parent="#accordionSidebar">
                                <li>
                                    <a class="{{ menuActivation(['client/whatsapp-settings'], 'active') }}"
                                        href="{{ route('client.whatsapp.settings') }}">{{ __('whatsapp') }}</a>
                                </li>
                                {{-- <li>
                                    <a class="{{ menuActivation(['client/rapiwa-settings'], 'active') }}"
                                        href="{{ route('client.rapiwa.settings') }}">{{ __('rapiwa_whatsapp') }}</a>
                                </li> --}}
                                @if (!empty(@auth()->user()->client->activeSubscription->telegram_access == '1'))
                                    @can('manage_telegram')
                                        <li>
                                            <a class="{{ menuActivation(['client/telegram-settings'], 'active') }}"
                                                href="{{ route('client.telegram.settings') }}">{{ __('telegram') }}</a>
                                        </li>
                                    @endcan
                                @endif

                                @php
                                    $omnisync = addon_is_activated('omnisync');
                                @endphp
                                @if ($omnisync)

                                    @if (!empty(@auth()->user()->client->activeSubscription->messenger_access == '1'))
                                        @can('manage_messenger')
                                        <li>
                                            <a class="{{ menuActivation(['client/messenger-settings'], 'active') }}" 
                                            href="{{ url('client/messenger-settings') }}">{{ __('messenger') }}</a>
                                        </li>
                                        @endcan
                                    @endif


                                    @if (!empty(@auth()->user()->client->activeSubscription->instagram_access == '1'))
                                        @can('manage_instagram')
                                        <li>
                                            <a class="{{ menuActivation(['client/instagram-settings'], 'active') }}" 
                                            href="{{ url('client/instagram-settings') }}">{{ __('instagram') }}</a>
                                        </li>
                                        @endcan
                                    @endif

                                @endif

                                @php
                                    $emirosync = addon_is_activated('emirosync');
                                @endphp
                                @if ($emirosync)
                                    <li>
                                        <a class="{{ menuActivation(['client/shop-settings'], 'active') }}" 
                                        href="{{ url('client/shop-settings') }}">{{ __('shop_settings') }}</a>
                                    </li>
                                    <li>
                                        <a class="{{ menuActivation(['client/payment-gateway-settings'], 'active') }}" 
                                        href="{{ url('client/payment-gateway-settings') }}">{{ __('payment_gateway') }}</a>
                                    </li>
                                @endif

                                <li>
                                    <a class="{{ menuActivation(['client/general-settings'], 'active') }}"
                                        href="{{ route('client.general.settings') }}">{{ __('general_setting') }}</a>
                                </li>

                                <li>
                                    <a class="{{ menuActivation(['client/billing/details'], 'active') }}"
                                        href="{{ route('client.billing.details') }}">{{ __('billing_details') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @php
                        $webhook = addon_is_activated('webhook');
                    @endphp
                    @if ($webhook)
                        <li class="{{ menuActivation(['client/webhook'], 'active') }}">
                            <a href="{{ route('client.settings.webhook') }}">
                                <i class="las la-paperclip"></i>
                                <span>{{ __('webhook') }}</span>
                            </a>
                        </li>
                   @endif

                   @if (setting('whatsapp_web_access') !== 'admin')
                   
                   <li class="p-2">{{ __('web') }}</li>
                    
                    <li class="{{ menuActivation(['client/web/overview*'], 'active') }}">
                        <a href="{{ route('client.web.overview') }}">
                            <i class="las la-layer-group"></i>
                            <span>{{ __('overview') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/devices*'], 'active') }}">
                        <a href="{{ route('client.web.devices') }}">
                            <i class="las la-mobile"></i>
                            <span>{{ __('Devices') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/whatsapp/verify-number*'], 'active') }}">
                        <a href="{{ route('client.web.whatsapp.verify-number.index') }}">
                            <i class="las la-book"></i>
                            <span>{{ __('verify_whatsapp') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/template*'], 'active') }}">
                        <a href="{{ route('client.web.templates.index') }}">
                            <i class="las la-file-alt"></i>
                            <span>{{ __('templates') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/whatsapp/campaigns*','client/web/whatsapp/campaign*'], 'active') }}">
                        <a href="{{ route('client.web.whatsapp.campaigns.index') }}">
                            <i class="las la-bullhorn"></i>
                            <span>{{ __('campaigns') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web-chat'], 'active') }}">
                        <a href="{{ route('client.web.chat.index') }}">
                            <i class="las la-sms"></i>
                            <span>{{ __('inbox') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/warm-up*'], 'active') }}">
                        <a href="{{ route('client.web.whatsapp.warm-up.index') }}">
                            <i class="las la-fire"></i>
                            <span>{{ __('warm_up') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/quick-reply*'], 'active') }}">
                        <a href="{{ route('client.web.whatsapp.quick-reply.index') }}">
                            <i class="las la-comment-dots"></i>
                            <span>{{ __('quick_replies') }}</span>
                        </a>
                    </li>
                    <li class="{{ menuActivation(['client/web/setting'], 'active') }}">
                        <a href="{{ route('client.web.setting') }}">
                            <i class="las la-cog"></i>
                            <span>{{ __('setting') }}</span>
                        </a>
                    </li>

                    @endif

                    @if (setting('whatsapp_web_access') == 'admin')
                    @if (!empty(@auth()->user()->client->activeSubscription->whatsapp_web_access == '1'))
                        <li class="p-2">{{ __('web') }}</li>
                        <li class="{{ menuActivation(['client/web/overview*'], 'active') }}">
                            <a href="{{ route('client.web.overview') }}">
                                <i class="las la-layer-group"></i>
                                <span>{{ __('overview') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/devices*'], 'active') }}">
                            <a href="{{ route('client.web.devices') }}">
                                <i class="las la-mobile"></i>
                                <span>{{ __('Devices') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/whatsapp/verify-number*'], 'active') }}">
                            <a href="{{ route('client.web.whatsapp.verify-number.index') }}">
                                <i class="las la-book"></i>
                                <span>{{ __('verify_whatsapp') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/template*'], 'active') }}">
                            <a href="{{ route('client.web.templates.index') }}">
                                <i class="las la-file-alt"></i>
                                <span>{{ __('templates') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/whatsapp/campaigns*','client/web/whatsapp/campaign*'], 'active') }}">
                            <a href="{{ route('client.web.whatsapp.campaigns.index') }}">
                                <i class="las la-bullhorn"></i>
                                <span>{{ __('campaigns') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web-chat'], 'active') }}">
                            <a href="{{ route('client.web.chat.index') }}">
                                <i class="las la-sms"></i>
                                <span>{{ __('inbox') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/warm-up*'], 'active') }}">
                            <a href="{{ route('client.web.whatsapp.warm-up.index') }}">
                                <i class="las la-fire"></i>
                                <span>{{ __('warm_up') }}</span>
                            </a>
                        </li>
                        <li class="{{ menuActivation(['client/web/quick-reply*'], 'active') }}">
                            <a href="{{ route('client.web.whatsapp.quick-reply.index') }}">
                                <i class="las la-comment-dots"></i>
                                <span>{{ __('quick_replies') }}</span>
                            </a>
                        </li>
                    @endif
                    @endif

                    {{-- <p>{{ __('api') }}</p> --}}
                    <li class="p-2">{{ __('api') }}</li>

                    <li class="{{ menuActivation(['client/api'], 'active') }}">
                        <a href="{{ route('client.settings.api') }}">
                            <i class="las la-paperclip"></i>
                            <span>{{ __('api') }}</span>
                        </a>
                    </li>

                </ul>
                
            </nav>
            
        </div>
    </header>
