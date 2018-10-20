@extends('layouts.main')
@section('styles')
    <style>

    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10">
                <div>
                    <a href="{{route('index')}}" class="btn btn-warning">Back to products</a>
                    <a href="{{route('edit_product',['id'=>$product->id])}}" class="btn btn-info">Edit product</a>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>{{$product->name}}</h2>
                        <span>{{$product->product_code}}</span>
                    </div>

                    <div class="w3-container">
                        <div class="col-sm-5">
                            <table class="w3-table w3-bordered">
                                <tr>
                                    <td><b>Size:</b></td>
                                    <td>{{$product->size?$product->size:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Brand:</b></td>
                                    <td>{{$product->brand?$product->brand:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Case count:</b></td>
                                    <td>{{$product->case_count?$product->case_count:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Created at:</b></td>
                                    <td>{{$product->created_at?$product->created_at->diffForHumans():''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Last modified:</b></td>
                                    <td>{{$product->updated_at?$product->updated_at->diffForHumans():''}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-12">
                            <p>{{$product->description}}</p>
                        </div>
                        <div class="col-sm-12">

                            <hr>
                            @if(count($product->attachments)>0)
                                @foreach($product->attachments as $attachment)
                                    <div class="col-sm-3">
                                    <img src="{{asset('/storage/'.$attachment->path)}}" style="width:100%" data-toggle="modal" data-target="#modal_{{$attachment->id}}">
                                        <!-- The Modal -->
                                        <div class="modal" id="modal_{{$attachment->id}}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <!-- Modal Header -->
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal Heading</h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>

                                                    <!-- Modal body -->
                                                    <div class="modal-body">
                                                        <img src="{{asset('/storage/'.$attachment->path)}}" style="width:100%">
                                                    </div>

                                                    <!-- Modal footer -->
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                No attachments found
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection