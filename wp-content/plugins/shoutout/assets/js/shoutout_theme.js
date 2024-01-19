shoutout_global__jQuery(function($){

function recordHit(value, shopName) {
    $.ajax({
        type: 'GET',
        crossDomain: true,
        url: 'https://www.shoutout.global/recordHit?shop=' + shopName + '&ref=' + value,
        success: function (result) {
            //console.log('p7');
        }
    });
}

function recordStandardHit(shopName) {
    $.ajax({
        type: 'GET',
        crossDomain: true,
        url: 'https://www.shoutout.global/recordstandardhit?shop=' + shopName,
        success: function (result) {
            //console.log('p8');
        }
    });
}
});

var cookieValue = checkCookie("p");
//console.log('cookieValue=' + cookieValue);

var shopName = '';
if (typeof Shopify != "undefined") {
    shopName = Shopify.shop;
};

if (cookieValue == false) {
    //no cookie set so check if url contains querystring
    var name = "p";
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS, "gi");
    var results = regex.exec(window.location.href);
    if (typeof results === 'undefined' || results === null) {
        //no affiliate value in query string so write general hit cookie
        //console.log('p1=1');
        setShortCookie('p', '1', shopName);
    } else {
        //coming from affiliate so set cookie for 90 days
        //console.log('p2=' + results[1]);
        setLongCookie('p', results[1], 90, shopName);
    }

} else {
    // Do nothing as cookie already set
    if (checkCookieValue("p") == "1") {
        //check if url contains a new p from affiliate, and if so overwrite cookie
        var name = "p";
        var regexS = "[\\?&]" + name + "=([^&#]*)";
        var regex = new RegExp(regexS, "gi");
        var results = regex.exec(window.location.href);

        if (typeof results === 'undefined' || results === null) {
            //console.log('p3=' + checkCookieValue("p"));
            //do nothing
        } else {
            //now coming from affiliate so set cookie for 90 days
            //console.log('p4=' + checkCookieValue("p"));
            setLongCookie('p', results[1], 90, shopName);
        }
    } else {
        //returning user with an affiliate tracking cookie looking at page again, may place order
        var name = "p";
        var regexS = "[\\?&]" + name + "=([^&#]*)";
        var regex = new RegExp(regexS, "gi");
        var results = regex.exec(window.location.href);

        if (typeof results === 'undefined' || results === null) {
            //do nothing
            //console.log('p5=' + checkCookieValue("p"));
        } else {
            //might be a newer affid so overwrite existing one if newer
            if (results[1] != checkCookieValue("p")) {
                //console.log('p6=' + results[1]);
                setLongCookie('p', results[1], 90, shopName); 
            }
        }
    }
};


//Helper Functions
function setLongCookie(cname, cvalue, exdays, shopName) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        recordHit(cvalue, shopName);
}

function setShortCookie(cname, cvalue, shopName) {
    document.cookie = cname + "=" + cvalue + ";path=/";
    recordStandardHit(shopName);
}

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
}

function checkCookie(cookieName) {
    var cname = getCookie(cookieName);
    if (cname != "") {
        return true;
    } else {
        return false;
    }
}

function checkCookieValue(cookieName) {
    var cname = getCookie(cookieName);
    if (cname != "") {
        return cname;
    } else {
        return cname;
    }
}
