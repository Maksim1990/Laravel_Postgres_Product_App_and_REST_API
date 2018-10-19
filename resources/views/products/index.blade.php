@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div>
                    <a href="{{route('create')}}" class="btn btn-info">Create new product</a>
                    <a href="{{route('import',['type'=>'csv'])}}" class="btn btn-success">Import products</a>
                </div>
                <div class="card">
                    <div class="card-header">OVERVIEW</div>

                    <div class="w3-container">


                        <table class="w3-table w3-striped w3-hoverable">
                            <tr>
                                <th>Product code</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Size</th>
                                <th>Case count</th>
                                <th></th>
                            </tr>
                            @if(!empty($products))
                                @foreach($products as $product)
                            <tr>
                                <td>{{$product->product_code}}</td>
                                <td><a href="{{route('product.show',['id'=>$product->id])}}">{{$product->name}}</a></td>
                                <td>{{$product->brand}}</td>
                                <td>{{$product->size}}</td>
                                <td>{{$product->case_count}}</td>
                                <td>
                                    <a href="{{route('edit_product',['id'=>$product->id])}}">Edit</a>
                                </td>
                            </tr>
                                @endforeach
                                @else
                                <tr>No products found</tr>
                            @endif

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section ('scripts')
    <script>
        @if(Session::has('product_change'))
        new Noty({
            type: 'warning',
            layout: 'topRight',
            text: '{{session('product_change')}}'

        }).show();
        @endif
    </script>
@endsection