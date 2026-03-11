function initiateBkash(orderID, amount) {
    var accessToken = '';

    $.ajax({
        url: "../bkash/token.php",
        type: 'POST',
        contentType: 'application/json',
        success: function (data) {
            console.log('token success');
            var obj = JSON.parse(data);
            accessToken = obj.id_token;

            bKash.init({
                paymentMode: 'checkout', //fixed value
                paymentRequest: {
                    amount: amount, //amount of the payment
                    intent: 'sale',
                    invoice: orderID
                },
                createRequest: function (request) { //called when click 'pay with bKash' button
                    $.ajax({
                        url: "../bkash/create.php?amount=" + request.amount + "&invoice=" + request.invoice,
                        type: 'GET',
                        contentType: 'application/json',
                        success: function (data) {
                            var obj = JSON.parse(data);

                            if (data && obj.paymentID != null) {
                                paymentID = obj.paymentID;
                                bKash.create().onSuccess(obj); //pass the whole response object in case of success
                            } else {
                                bKash.create().onError();
                                alert(obj.errorMessage);
                            }
                        },
                        error: function () {
                            bKash.create().onError();
                        }
                    });
                },
                executeRequestOnAuthorization: function () { //called when user enters right OTP and PIN
                    $.ajax({
                        url: "../bkash/execute.php?paymentID=" + paymentID,
                        type: 'GET',
                        contentType: 'application/json',
                        success: function (data) {
                            var obj = JSON.parse(data);

                            if (data && obj.paymentID != null) {
                                // Payment successful
                                // Re-using the success logic from checkout/index.php
                                finalizeOrder(orderID);
                            } else {
                                bKash.execute().onError();
                                alert(obj.errorMessage);
                            }
                        },
                        error: function () {
                            bKash.execute().onError();
                        }
                    });
                },
                onClose: function () {
                    alert('bKash payment window closed.');
                }
            });

            // Trigger click on bKash button (bKash script generates one or binds to something)
            // Actually, we can manually trigger it if we want, or bKash.init will show the frame.
            // For tokenized checkout, we usually click the bKash button.
            // Since we are calling initiateBkash after order creation, we can trigger the frame.
            
            // In some versions, bKash.init() doesn't automatically open the frame.
            // We might need to handle the trigger.
            console.log("bKash initialized for order: " + orderID);
            
            // To automatically open the payment window after init:
            $('#bKash_button').click(); 
        },
        error: function () {
            console.log('token error');
            alert('বিকাশ পেমেন্ট শুরু করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।');
        }
    });
}

function finalizeOrder(orderID) {
    // Clear Cart
    const checkoutType = window.checkoutType || 'buy';
    if (checkoutType === 'borrow') {
        localStorage.removeItem('antyam_borrow_cart');
    } else {
        localStorage.removeItem('antyam_cart');
    }

    // Update Success UI
    document.getElementById('order-id-display').innerText = '#' + orderID;

    const modal = document.getElementById('success-modal');
    const overlay = document.getElementById('modal-overlay');
    const content = document.getElementById('modal-content');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        overlay.classList.add('opacity-100');
        content.classList.remove('scale-90', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
        if(typeof createConfetti === 'function') createConfetti();
    }, 100);
}

// Add a hidden bKash button to trigger the frame
$(document).ready(function() {
    $('body').append('<button id="bKash_button" style="display:none;">Pay With bKash</button>');
});
