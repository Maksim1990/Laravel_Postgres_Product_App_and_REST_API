@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div>
                    <a href="{{route('index')}}" class="btn btn-warning">Back to products</a>
                </div>
                <div class="card">
                    <div class="w3-container w3-margin-bottom">
                        <table class="w3-table w3-striped w3-hoverable">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Subcategory</th>
                                <th></th>
                            </tr>
                            @if(count($categories)>0)
                                @foreach($categories as $category)
                                    <tr id="category_item_{{$category->id}}">
                                        <td>{{$category->id}}</td>
                                        <td>{{$category->name}}</td>
                                        <td>{{!empty($arrSubCategories[$category->id])?$arrSubCategories[$category->id]['name']:"No parent category"}}</td>
                                        <td>
                                            <a href="#" class="btn btn-danger" data-parent="{{!empty($arrSubCategories[$category->id])?$arrSubCategories[$category->id]['id']:0}}"
                                               id="delete_{{$category->id}}"
                                               data-toggle="modal"
                                               data-target="#deleteModal_category">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        No categories found
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="category_id" value="">
        <input type="hidden" id="parent_id" value="">
    </div>
    @include('partials.modal_attachment_delete',['attachment'=>'category',
        'action'=>'delete',
        'slug'=>'category and all subcategories'])
@endsection
@section ('scripts')
    <script>

        {{--new Noty({--}}
            {{--type: 'warning',--}}
            {{--layout: 'topRight',--}}
            {{--text: '{{session('product_change')}}'--}}

        {{--}).show();--}}

        var token = '{{\Illuminate\Support\Facades\Session::token()}}';
        $("button[id^='delete_attachment_']").click(function () {
            var category_id=$('#category_id').val();
            var parent_id=$('#parent_id').val();

            var arrCategoriesToDelete=[category_id];
            if(parent_id>0){
                arrCategoriesToDelete.push(parent_id);
            }
            var url = '{{ route('delete_categories_ajax') }}';
            $.ajax({
                method: 'POST',
                url: url,
                dataType: "json",
                data: {
                    arrCategoriesToDelete: JSON.stringify(arrCategoriesToDelete),
                    _token: token
                }, beforeSend: function () {
                    //-- Show loading image while execution of ajax request
                    $("div#divLoading").addClass('show');
                },
                success: function (data) {
                    if (data[0]['success']) {
                        new Noty({
                            type: 'success',
                            layout: 'topRight',
                            text: 'Categories and relevant subcategories were deleted!'
                        }).show();

                        //-- Remove category
                        $('#category_item_'+category_id).remove();

                        //-- Check
                        if(parent_id>0){
                            $('#category_item_'+parent_id).remove();
                        }
                    } else {
                        new Noty({
                            type: 'error',
                            layout: 'bottomLeft',
                            text: data['error']
                        }).show();
                    }

                    //-- Hide loading image
                    $("div#divLoading").removeClass('show');
                }
            });



        });
        $("a[id^='delete_']").click(function () {
            var category_id = $(this).attr('id').replace('delete_', '');
            var parent_id = $(this).data('parent');

            $('#category_id').val(category_id);
            $('#parent_id').val(parent_id);


        });

    </script>
@endsection