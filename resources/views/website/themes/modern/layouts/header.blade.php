<!-- Header Start -->
<header class="header">
    <nav class="nav">
        <div class="container">
            <div class="header__wrapper">
                <!-- Header Logo End -->
                <div class="header__logo">
                    @php
                        $src = setting('light_logo') && @is_file_exists(setting('light_logo')['original_image']) ? get_media(setting('light_logo')['original_image']) : get_media('images/default/logo/logo-green-white.png');
                    @endphp
                    <a href="{{url('/')}}">
                        <img src="{{ $src }}" alt="logo" />
                    </a>
                </div>
                <!-- Header Logo End -->
                <!-- Header Menu Start -->
                <div class="header__menu">
                    <ul class="main__menu">
                        {{-- <li><a href="{{ route('home') }}">{{__('home') }}</a></li> --}}
                        @if(setting('show_default_menu_link') == 1)
                            @if($menu_language && is_array(setting('header_menu')) ? count(setting('header_menu')) : 0 != 0 && setting('header_menu') != [])
                                @foreach($menu_language as $key => $value)
                                    <li><a href="{{ @$value['url']  }}">{{ @$value['label'] }}</a></li>
                                @endforeach
                            @endif
                        @endif 

                        <li class="has__dropdown position-static">
                            <a href="#">{{__('use_cases') }}</a>
                            
                            <div class="sub__menu mega__menu">
                                <div class="grid-2">
                                    @if (!empty($use_cases) && is_iterable($use_cases))
                                        @foreach ($use_cases as $use_case)
                                            <a href="{{ $use_case->link }}" class="menu__item">
                                                <div class="icon">
                                                    <img style="width: 20px; height: 20px;" src="{{ getFileLink('original_image', $use_case->image) }}" alt="feature" />
                                                </div>
                                                <div class="content">
                                                    <h4 class="title">{{ @$use_case->language->title }}</h4>
                
                                                    @if (!empty($use_case->language->description) && is_iterable($use_case->language->description))
                                                        @foreach ($use_case->language->description as $description)
                                                            <p class="desc">{!! $description !!}</p>
                                                        @endforeach
                                                    @else
                                                        {{-- <p class="desc">No description available</p> --}}
                                                    @endif
                
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        
                                    @endif
                                    
                                </div>
                            </div>
                        </li>
                        
                        <li class="has__dropdown position-static">
                            <a href="#">{{__('more') }}</a>
                            <div class="sub__menu mega__menu">
                                <div class="grid-2">
                                    @if (!empty($nav_mores) && is_iterable($nav_mores))
                                        @foreach ($nav_mores as $nav_more)
                                            <a href="{{ $nav_more->link }}" class="menu__item">
                                                <div class="icon">
                                                    <img style="width: 20px; height: 20px;" src="{{ getFileLink('original_image', $nav_more->image) }}" alt="feature" />
                                                </div>
                                                <div class="content">
                                                    <h4 class="title">{{ @$nav_more->language->title }}</h4>
                
                                                    @if (!empty($nav_more->language->description) && is_iterable($nav_more->language->description))
                                                        @foreach ($nav_more->language->description as $description)
                                                            <p class="desc">{!! $description !!}</p>
                                                        @endforeach
                                                    @else
                                                        {{-- <p class="desc">No description available</p> --}}
                                                    @endif
                
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        
                                    @endif
                                    
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- Header Menu End -->
                <!-- Header Meta Start -->
                <div class="header__meta">
                    <div class="meta__list">
                        <?php
                        $language_switcher = setting('language_switcher');
                        if ($language_switcher == '') {
                            $language_switcher = 1;
                        }
                        ?>
                        @if ($language_switcher)
                            <div class="language__dropdown">
                                @php
                                    $active_locale = '';
                                    $languages = app('languages');
                                    $locale_language = $languages->where('locale', app()->getLocale())->first();
                                    if ($locale_language) {
                                        $active_locale = $locale_language->name;
                                    }
                                @endphp
                                <a href="#" class="selected">{{ $active_locale }}</a>
                                <ul class="language__list dropdown__list">
                                    @foreach ($languages as $language)
                                        <li><a href="{{ setLanguageRedirect($language->locale) }}">{{ $language->name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="header__btn">
                            @if (Auth::check())
                                @if (Auth::user()->role_id == 1)
                                    <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">{{ __('dashboard') }}</a>
                                @else
                                    <a class="btn btn-primary" href="{{ route('client.dashboard') }}">{{ __('dashboard') }}</a>
                                @endif
                            @else
                                <a class="btn btn-primary" href="{{ route('login') }}">{{ __('login') }}</a>
                            @endif
                        </div>
                        <!-- Header Toggle Start -->
                        <div class="header__toggle">
                            <div class="toggle__bar"></div>
                        </div>
                        <!-- Header Toggle End -->
                    </div>
                </div>
                <!-- Header Meta End -->
            </div>
        </div>
    </nav>
</header>
<!-- Header End -->