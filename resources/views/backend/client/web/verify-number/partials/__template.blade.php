@php
    use App\Helpers\TextHelper;
@endphp
<div class="accordion border-0" id="accordionPreview">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePreview"
                aria-expanded="true" aria-controls="collapsePreview">
                {{ __('preview') }}
            </button>
        </h2>
        <div id="collapsePreview" class="accordion-collapse collapse show" data-bs-parent="#accordionPreview">
            <div class="accordion-body">
                <div class="whatsapp-preview">
                    {{-- <div class="marvel-device nexus5"> --}}
                    <div class="temp-pre">
                        <div class="screen ">
                            <div class="screen-container">
                                <div class="screen-container">
                                    <div class="chat">
                                        <div class="chat-container">
                                            <div class="conversation">
                                                <div class="conversation-container">
                                                    <div class="message received card">
                                                        <div class="card-body m-0 p-0">
                                                            <div id="message-header" class="message-header py-2">
                                                                @if (!empty($data->message_type))
                                                                    @switch($data->message_type)
                                                                        @case('document')
                                                                            @if (isset($data->media_url))
                                                                                <embed
                                                                                    src="{{ $data->media_url }}"
                                                                                    width="auto" height="auto"
                                                                                    type="application/pdf">
                                                                            @endif
                                                                        @break

                                                                        @case('image')
                                                                            @if (isset($data->message_type))
                                                                                <img src="{{ $data->media_url }}"
                                                                                    alt="Header image" style="">
                                                                            @endif
                                                                        @break

                                                                        @case('video')
                                                                            @if (isset($data->message_type))
                                                                                <video width="100%" height="200" controls>
                                                                                    <source
                                                                                        src="{{ $data->message_type }}"
                                                                                        type="video/mp4">
                                                                                </video>
                                                                            @endif
                                                                        @break

                                                                        @case('audio')
                                                                            @if (isset($data->message_type))
                                                                                <audio controls>
                                                                                    {{-- <source src="horse.ogg" type="audio/ogg"> --}}
                                                                                    <source
                                                                                        src="{{ $data->message_type }}"
                                                                                        type="audio/mpeg">
                                                                                
                                                                                </audio>
                                                                            @endif
                                                                        @break
                                                                        
                                                                    @endswitch
                                                                @endif
                                                            </div>
                                                            <div class="message-body mb-2">
                                                                <div id="_message_body_text">
                                                                    @if (!empty($data->message) && !empty($data->message))
                                                                        <?php
                                                                            $_body = $data->message;
                                                                        ?>
                                                                        <p class="card-text v-body">
                                                                            {!! TextHelper::transformText($_body) !!}
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="message-footer">
                                                                <div id="_footer_text">
                                                                    Thank you for choosing us! Contact support
                                                                    for assistance.
                                                                </div>
                                                                <span class="metadata">
                                                                    <span class="time">12:00 AM</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
