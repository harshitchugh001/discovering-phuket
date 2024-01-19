function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};


var partnerID = getCookie('p'); 
if (typeof Shopify != "undefined") {
    if (typeof Shopify.checkout != "undefined") {
        if (typeof Shopify.checkout.order_id != "undefined") {
            if (partnerID != '') {
                if (partnerID != '1') {
                    var varStr = "order_id=" + Shopify.checkout.order_id + "&shop=" + Shopify.shop + "&first_name=" + Shopify.checkout.shipping_address.first_name + "&last_name=" + Shopify.checkout.shipping_address.last_name + "&total_price=" + Shopify.checkout.total_price + "&currency=" + Shopify.checkout.currency + "&partnerID=" + partnerID + "&email=" + Shopify.checkout.email;
                    var url = "https://webhook.site/bf61196b-b7d5-4386-b3a2-576be9136616"
                    makeCorsRequest(url, varStr);
                }
            }
        }
    }
}


function createCORSRequest(method, url) {
    var xhr = new XMLHttpRequest();
    if ("withCredentials" in xhr) {
        // XHR for Chrome/Firefox/Opera/Safari.
        xhr.open(method, url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    } else if (typeof XDomainRequest != "undefined") {
        // XDomainRequest for IE.
        xhr = new XDomainRequest();
        xhr.open(method, url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    } else {
        // CORS not supported.
        xhr = null;
    }
    return xhr;
}

// Make the actual CORS request.
function makeCorsRequest(url,params) {
    var xhr = createCORSRequest('POST', url);
    if (!xhr) {
        //alert('CORS not supported');
        return;
    }

    // Response handlers.
    xhr.onload = function () {
        var text = xhr.responseText;
        //console.log('Data sent');
    };

    xhr.onerror = function () {
        //console.log('Error!');
    };

    xhr.send(params);
}