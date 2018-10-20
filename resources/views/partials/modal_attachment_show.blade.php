
<!-- Show Modal -->
<div class="modal" id="modal_{{$attachment->id}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{$attachment->name}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @if(in_array($attachment->extension,\App\Config\Config::IMAGES_EXTENSIONS))
                    <img src="{{asset($attachment->path)}}"
                         style="width:100%">
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                        data-dismiss="modal">Close
                </button>
            </div>
        </div>
    </div>
</div>
</div>