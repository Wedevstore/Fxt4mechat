<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @php
        $icon = setting('favicon');
    @endphp
    @if ($icon)
        <link rel="apple-touch-icon" sizes="57x57"
              href="{{ $icon != [] && @is_file_exists($icon['image_57x57_url']) ? static_asset($icon['image_57x57_url']) : static_asset('images/default/favicon/favicon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60"
              href="{{ $icon != [] && @is_file_exists($icon['image_60x60_url']) ? static_asset($icon['image_60x60_url']) : static_asset('images/default/favicon/favicon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72"
              href="{{ $icon != [] && @is_file_exists($icon['image_72x72_url']) ? static_asset($icon['image_72x72_url']) : static_asset('images/default/favicon/favicon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76"
              href="{{ $icon != [] && @is_file_exists($icon['image_76x76_url']) ? static_asset($icon['image_76x76_url']) : static_asset('images/default/favicon/favicon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114"
              href="{{ $icon != [] && @is_file_exists($icon['image_114x114_url']) ? static_asset($icon['image_114x114_url']) : static_asset('images/default/favicon/favicon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120"
              href="{{ $icon != [] && @is_file_exists($icon['image_120x120_url']) ? static_asset($icon['image_120x120_url']) : static_asset('images/default/favicon/favicon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144"
              href="{{ $icon != [] && @is_file_exists($icon['image_144x144_url']) ? static_asset($icon['image_144x144_url']) : static_asset('images/default/favicon/favicon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152"
              href="{{ $icon != [] && @is_file_exists($icon['image_152x152_url']) ? static_asset($icon['image_152x152_url']) : static_asset('images/default/favicon/favicon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180"
              href="{{ $icon != [] && @is_file_exists($icon['image_180x180_url']) ? static_asset($icon['image_180x180_url']) : static_asset('images/default/favicon/favicon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"
              href="{{ $icon != [] && @is_file_exists($icon['image_192x192_url']) ? static_asset($icon['image_192x192_url']) : static_asset('images/favicon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="32x32"
              href="{{ $icon != [] && @is_file_exists($icon['image_32x32_url']) ? static_asset($icon['image_32x32_url']) : static_asset('images/default/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96"
              href="{{ $icon != [] && @is_file_exists($icon['image_96x96_url']) ? static_asset($icon['image_96x96_url']) : static_asset('images/default/favicon/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="16x16"
              href="{{ $icon != [] && @is_file_exists($icon['image_16x16_url']) ? static_asset($icon['image_16x16_url']) : static_asset('images/default/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ static_asset('images/default/favicon/manifest.json') }}">

        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage"
              content="{{ $icon != [] && @is_file_exists($icon['image_144x144_url']) ? static_asset($icon['image_144x144_url']) : static_asset('images/default/favicon/favicon-144x144.png') }}">
    @else
        <link rel="shortcut icon" href="{{ static_asset('images/default/favicon/favicon-96x96.png') }}">
    @endif
    <title>Paddle</title>
</head>
<body>
<div style="text-align: center">
    <div>
        <a href="{{ url()->previous() }}" onclick="return confirm('{{ __('are_you_sure') }}?')">{{ __('cancel_payment') }}</a>
    </div>
    <div class="paddle-container"></div>
</div>
<script src="{{ static_asset('admin/js/jquery.min.js') }}"></script>
<script src="{{ static_asset('client/js/paddle.js') }}"></script>
<script>
    @if(setting('is_paddle_sandbox_mode_activated'))
        Paddle.Environment.set("sandbox");
    @endif
    Paddle.Setup({
        token: '{{ setting('paddle_client_token') }}',

        eventCallback : function(data) {
            if (data.name == 'checkout.completed') {
                data._token = '{{ csrf_token() }}';
                data.trx_id = '{{ $trx_id }}';
                data.plan_id = '{{ $plan->id }}';

                $.ajax({
                    url : '{{ route('client.paddle.payment.success') }}',
                    type : 'POST',
                    data : data,
                    success : function(response) {
                        if (response.success) {
                            window.location.href = response.route;
                        }
                        else {
                            window.location.href = response.route;
                        }
                    }
                });
            }
        }
    });
    let config = {
        settings: {
            displayMode: "inline",
            theme: "light",
            locale: "en",
            frameTarget: "paddle-container",
            frameInitialHeight: "1200",
            frameStyle: "width: 100%; min-width: 312px; background-color: transparent; border: none;",
        },
        items: [
            {
                priceId: '{{ $price_id }}',
                quantity: 1
            },
        ],

    };
    @if($client->paddle_customer_id)
        config.customer = {
            id : "{{ $client->paddle_customer_id }}"
        };
    @endif
    Paddle.Checkout.open(config);
</script>
</body>
</html>