<section class="container-fluid contact-form">
    <div class="container text-center contact-inner">
        @include('flash-message')
        <h2 class="style-h2 pb-4">Contact Us</h2>
        <form action="{{route('contactus.store')}}" method="POST" id="contact-form">
            @csrf
            <div class="row ">
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <input type="text" class="form-control" name="name" placeholder="Name" required />

                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <input type="Email" class="form-control" name="email" placeholder="Email" required />

                    </div>

                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <input type="number" class="form-control" name="number" placeholder="Phone Number" required />

                    </div>
                </div>
                <div class="col-md-6 col-sm-6">

                    <div class="form-group">
                        <input type="Text" class="form-control" name="address" placeholder="Address" required />

                    </div>

                </div>
                <div class="col-md-12 col-sm-12">

                    <div class="form-group">
                        <textarea class="form-control" rows="4" name="message" placeholder="Message" required> </textarea>

                    </div>

                </div>
                <div class="col-lg-12 col-sm-12 text-center">
                    <button type="submit" class="btn ">SEND</button>
                </div>

                <!-- <button type="submit" class="btn text-center">SEND</button> -->
            </div>

        </form>
    </div>
    <div id="loader"></div>
    <script>
        $('#contact-form').submit(function() {
            $('#loader').css('visibility', 'visible');
        });
    </script>
</section>