<script>
    var token = '{{\Illuminate\Support\Facades\Session::token()}}';

    function getCategoriesList(strExcludeCat) {
        var availableTags = '';
        var url = '{{ route('get_categories_ajax') }}';
        $.ajax({
            method: 'POST',
            url: url,
            dataType: "json",
            async: false,
            data: {
                strExcludeCat: strExcludeCat,
                _token: token
            },
            success: function (data) {
                availableTags = data[0]['arrCategories'];
            }
        });
        return availableTags
    }

    $("#categories,#subcategories").click(function () {
        var id = $(this).attr('id');
        if (id === 'categories') {
            var strExcludeCat = $('#subcategories').val();
        } else {
            var strExcludeCat = $('#categories').val();
        }

        $(this).autocomplete({
            source: getCategoriesList(strExcludeCat)
        });
    });

    function deleteCategory(elm) {
        var strCategory = $(elm).data('category');
        $('#category_delete').val(strCategory);
    }


    $("#delete_attachment_category").click(function (e) {
        var strCategory = $('#category_delete').val();

        //-- Get value of the currently selected categories
        var oldSelectedCategories = $('#categories_form').val();
        var arrOldSelectedCategories = oldSelectedCategories.split(";");

        Array.prototype.remove = function () {
            var what, a = arguments, L = a.length, ax;
            while (L && this.length) {
                what = a[--L];
                while ((ax = this.indexOf(what)) !== -1) {
                    this.splice(ax, 1);
                }
            }
            return this;
        };


        if (arrOldSelectedCategories.length > 0) {
            for (var i = 0; i < arrOldSelectedCategories.length; i++) {
                if (strCategory === arrOldSelectedCategories[i]) {
                    arrOldSelectedCategories.remove(strCategory)
                }
            }
        }

        //-- Rebuild string of currently selected categories
        var strCategories = '';
        if (arrOldSelectedCategories.length > 0) {
            strCategories = arrOldSelectedCategories.join(";");
        }
        $('#categories_form').val(strCategories.trim());


        $('.category_text').each(function (index) {
            if (strCategory === $(this).text()) {
                $(this).parent().remove();
            }
        });
    });


    $("#add_category").click(function (e) {
        e.preventDefault();
        var status = true;
        $('#categories_alert').text('');
        var category = $('#categories').val();
        var subcategory = $('#subcategories').val();

        if (category === '') {
            status = false;
            $('#categories_alert').text('* Please choose category');
        }

        if (subcategory !== '') {
            category += ":" + subcategory;
        }

        $('.category_text').each(function (index) {
            if (category === $(this).text()) {
                status = false;
                new Noty({
                    type: 'error',
                    layout: 'bottomLeft',
                    text: 'Such category already selected!'
                }).show();
            }
        });


        if (status) {
            $('#categories,#subcategories').val('');

            var strCategory = "<div class=\"w3-display-topright\">\n" +
                "                    <button class=\"btn delete\" data-category='" + category + "' onclick=\"deleteCategory(this)\" data-toggle=\"modal\"\n" +
                "                                            data-target=\"#deleteModal_category\">X\n" +
                "                    </button>\n" +
                "                    </div>\n" +
                "                    <div class=\"w3-display-middle category_text\">" + category + "</div>";

            $('<div class="w3-display-container w3-green col-sm-3 w3-margin-right w3-margin-bottom">').html(strCategory + "</div>").appendTo('#categories_list');
        }
        $('#categories,#subcategories').val('');


    });


    $('#product_form').on('submit', function (e) {
        e.preventDefault();

        var oldSelectedCategories = $('#categories_form').val();
        var arrOldSelectedCategories = oldSelectedCategories.split(";");
        var arrCategories = [];
        $('.category_text').each(function (index) {
            if (!arrOldSelectedCategories.includes($(this).text())) {
                arrCategories.push($(this).text());
            }
        });
        var strCategories = '';
        if (arrCategories.length > 0) {
            strCategories = ";" + arrCategories.join(";");
        }
        var strCategories = oldSelectedCategories + strCategories;
        $('#categories_form').val(strCategories.trim());
        var blnStatus = checkIfAnyResourceAttached();
        if (blnStatus) {
            this.submit();
        }else{
            new Noty({
                type: 'error',
                layout: 'bottomLeft',
                text: 'At least one file should be attached to product'
            }).show();
        }
    });

    function checkIfAnyResourceAttached() {
        var url = '{{ route('check_attached_resource_ajax') }}';
        var product_id = $('#product_id').val();
        var status = false;
        $.ajax({
            method: 'POST',
            url: url,
            dataType: "json",
            async: false,
            data: {
                product_id: product_id,
                _token: token
            },
            success: function (data) {
                status = data[0]['status'];
            }
        });
        return status;
    }
</script>