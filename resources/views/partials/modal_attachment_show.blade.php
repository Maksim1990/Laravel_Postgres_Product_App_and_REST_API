
<!-- Show Modal -->
<div class="modal" id="modal_{{$attachment->id}}">
    <div class="modal-dialog w3-center">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{$attachment->name}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @if(in_array($attachment->extension,\App\Config\Config::IMAGES_EXTENSIONS))
                    <img src="{{asset($attachment->path)}}" width="840">
                @elseif(in_array($attachment->extension,\App\Config\Config::VIDEO_EXTENSIONS))
                    <video controls
                           preload="auto" width="840" height="464">
                        <source src="{{ asset($attachment->path) }}" type='video/mp4'>
                        <p>To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                    </video>
                @endif
                    <hr>
                <div class="col-sm-6 col-sm-offset-3 w3-center">
                    <p class="caption_text" id="caption_text_{{$attachment->id}}">
                        {{!empty($attachment->caption)?$attachment->caption:'Still no caption'}}
                    </p>
                    <p class="caption_input" id="caption_input_box_{{$attachment->id}}">
                        <input type="text" class="form-control" id="caption_input_{{$attachment->id}}" value="">
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                        data-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>