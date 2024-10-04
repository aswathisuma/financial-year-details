@extends('default.app')
@section('content')
<!-- slider section -->
<div class="hero_area">
    <section class="slider_section ">
        <div class="container ">
            
            <div class="find_container ">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <form id="financial-year-form">
                                @csrf
                                <div class="form-row ">                                    
                                    <div class="form-group col-lg-4">
                                        <select name="country" class="form-control wide" id="country">
                                            <option value="">Country</option>
                                            <option value="uk">UK</option>
                                            <option value="ireland">Ireland</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <select name="year" class="form-control wide" id="year">
                                            <option value="">Year</option>
                                            @foreach($years as $year)
                                            <option>{{$year}}</option>
                                            @endforeach
                                        </select>
                                    </div>                                    
                                    <div class="form-group col-lg-4">
                                        <div class="btn-box">
                                            <button type="submit" class="btn ">Submit</button>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-12" id="output" style="color: white;"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </section>
</div>
<!-- end slider section -->
@endsection
@section('script')
<script>
    $(document).ready(function(){
        $(document).ready(function() {
            $('#financial-year-form').validate({
                rules: {
                    country: {
                        required: true
                    },
                    year: {
                        required: true
                    }
                },
                messages: {
                    country: {
                        required: "Please select a country."
                    },
                    year: {
                        required: "Please select a year."
                    }
                },
                submitHandler: function(form) {
                    var country = $('#country').val(); 
                    var year = $('#year').val();
                    $.ajax({
                        url: '{{route("financial-year")}}',
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            country: country,
                            year: year
                        },
                        success: function (data) {
                            $('#output').html('');
                            $('#output').append('<p>Financial Year Start: ' + data.financial_year_start + '</p>');
                            $('#output').append('<p>Financial Year End: ' + data.financial_year_end + '</p>');
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#output').html('<p>Error: ' + errorThrown + '</p>');
                        }
                    });
                }
            });
        });

    });
    
</script>
@endsection