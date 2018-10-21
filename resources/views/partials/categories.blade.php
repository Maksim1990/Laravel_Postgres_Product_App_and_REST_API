<div class="w3-margin-bottom">
    <div class="col-sm-12">
        <div class="ui-widget">
            <div class="col-sm-4 tooltip_cust">
                <input id="categories" type="text" class="form-control"
                       placeholder="Start type category">
                <span class="tooltiptext">Category</span>
                <span class="w3-text-red" data-placement="top" id="categories_alert"></span>
            </div>
            <div class="col-sm-4 tooltip_cust">
                <input id="subcategories" type="text" class="form-control"
                       placeholder="Start type subcategory">
                <span class="tooltiptext">Subcategory</span>
            </div>
            <div class="col-sm-2">
                <a href="#" id="add_category" class="btn btn-success">Add</a>
            </div>
            <div class="col-sm-12">
                <hr>
                <div id="categories_list">
                    @if(old('categories_form'))
                        @php
                            $categories=explode(";",old('categories_form'));
                        @endphp
                        @foreach($categories as $category)
                            <div class="w3-display-container w3-green col-sm-3 w3-margin-right w3-margin-bottom">
                                <div class="w3-display-topright">
                                    <button class="btn delete" data-category="{{$category}}" onclick="deleteCategory(this)"
                                            data-toggle="modal"
                                            data-target="#deleteModal_category">X
                                    </button>
                                </div>
                                <div class="w3-display-middle category_text">{{$category}}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>