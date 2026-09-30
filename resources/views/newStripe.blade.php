<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Page - HackTheStuff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .credit-card-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }

        .alert-danger {
            margin-top: 20px;
        }

        #card-element {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background-color: #fff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Stripe Payment Page - HackTheStuff</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="credit-card-box">
                    <div class="card-title">Payment Details</div>
                    @if (Session::has('success'))
                    <div class="alert alert-success text-center">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <p>{{ Session::get('success') }}</p>
                    </div>
                    @endif
                    <form role="form" action="{{ route('stripe.post') }}" method="post" id="payment-form">
                        @csrf
                        <div class="mb-3">
                            <label for="name-on-card" class="form-label">Name on Card</label>
                            <input type="text" class="form-control" id="name-on-card" required>
                        </div>
                        <div class="mb-3">
                            <label for="card-element" class="form-label">Credit or Debit Card</label>
                            <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                        </div>
                        <div class="mb-3 error alert alert-danger hide">
                            <div class="alert">Please correct the errors and try again.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Pay Now ($100)</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var stripe = Stripe('your_new_stripe_publishable_key');
            var elements = stripe.elements();

            var style = {
                base: {
                    color: '#32325d',
                    lineHeight: '18px',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            var card = elements.create('card', { style: style });
            card.mount('#card-element');

            card.addEventListener('change', function(event) {
                var displayError = document.querySelector('.error');
                if (event.error) {
                    displayError.classList.remove('hide');
                    displayError.textContent = event.error.message;
                } else {
                    displayError.classList.add('hide');
                    displayError.textContent = '';
                }
            });

            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        var errorElement = document.querySelector('.error');
                        errorElement.classList.remove('hide');
                        errorElement.textContent = result.error.message;
                    } else {
                        stripeTokenHandler(result.token);
                    }
                });
            });

            function stripeTokenHandler(token) {
                var form = document.getElementById('payment-form');
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                form.submit();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
