//
// Helper functions
//
var httpGet = function(url, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            callback(xmlHttp.responseText);
        }
    };
    xmlHttp.open("GET", url, true); // true for asynchronous
    xmlHttp.send();
};

var httpPost = function(url, data, callback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            callback(xmlHttp.responseText);
        }
    };
    xmlHttp.open("POST", addUrl, true); // true for asynchronous
    xmlHttp.setRequestHeader("Content-type", "application/json");
    var message = JSON.stringify(data);
    xmlHttp.send(message);
};

//
//Start processing
//
var path = window.location.href;

if (path.indexOf('/products/') !== -1) {

    var addToCartWrapper = document.getElementById("AddToCartWrapper");

    var baseUrl = path.substr(0, path.indexOf('/products'));
    if( path.indexOf("stampnouveau") != -1) {
        baseUrl = "https://stampnouveau.com";
    }

    var productHandle = path.match(/\/products\/([a-z0-9\-]+)/)[1];
    var productUrl = "/products/" + productHandle + ".js";

    var variants = [];
    var currentVariant = {};
    var selectElements = document.getElementsByClassName("single-option-selector");

    //set up the Personalize button, but hide it for now
    var button = document.getElementById("product-add-to-cart");
    if ( button == null ) {
        button = document.getElementById("AddToCart");
    }
    if ( button == null ) {
        button = document.getElementById("add-to-cart");
    }
    if ( button == null ) {
        button = document.getElementById("add");
    }
    var button2 = button.cloneNode(true);
    button2.style.display = "none";
    var buttonText = button2.children[0];
    if(!buttonText) {
        button2.value = "Personalize";
    } else {
        buttonText.id = "PersonalizeText";
        buttonText.innerHTML = "Personalize";
    }
    var form = document.getElementById("add-to-cart-form");
    if ( form == null ) {
        form = document.getElementById("AddToCartForm");
    }
    form.appendChild(button2);

    httpGet(productUrl, function (result) {
        var product = JSON.parse(result);
        var barcode = product.variants[0].barcode;

        variants = product.variants;
        var match = true;
        var setCurrentVariant = function() {
            addToCartWrapper ? addToCartWrapper.style.display = "" : button.style.display = "";
            button2.style.display = "none";
            for(var j = 0; j < variants.length; j++) {
                match = true;
                for(var k = 0; k < variants[j].options.length; k++) {
                    if (selectElements.length > 0 && variants[j].options[k] != selectElements[k].value) match = false;
                }
                if(match) {
                    currentVariant = variants[j];
                    console.debug("current variant: " + currentVariant.barcode);
                    break;
                }
            }
            if(match && currentVariant.barcode != null && currentVariant.barcode != "") {
                console.debug("personalize");
                addToCartWrapper ? addToCartWrapper.style.display = "none" : button.style.display = "none";
                button2.setAttribute('formaction', "https://design.gdtimagepro.com/shopify/shopifysetup.php?code=" + currentVariant.barcode +
                    "&shop=" + Shopify.shop +
                    "&submitUrl=" + baseUrl + "/cart?productId=" + currentVariant.id);
                button2.method = "post"; //the method is post so that it doesn't append stuff to the url
                button2.style.display = "";
            }
        };

        setCurrentVariant();

        for(var i = 0; i < selectElements.length; i++) {
            selectElements[i].addEventListener("change", setCurrentVariant);
        }
    });
}
else if (path.indexOf('/cart') !== -1) {
    var baseUrl = path.substr(0, path.indexOf('/cart'));
    if ((path.indexOf('?') !== -1) && (path.indexOf('productId')!== -1) && (path.indexOf('OrderId') !== -1)) {
        var addUrl = baseUrl + "/cart/add.js";

        var paramstr = decodeURIComponent(path.substr(path.indexOf('?') + 1));
        var params = paramstr.split('&');
        var paramObj = {};
        for (var i = 0; i < params.length; i++) {
            var pair = params[i].split("=");
            paramObj[pair[0]] = pair[1];
        }
        var orderDetails = JSON.parse(paramObj.orderDetails);

        var data = {quantity: 1, id: paramObj.productId, properties: {orderId: orderDetails.orderItemId, _imageId: paramObj.OrderId}};
        httpPost(addUrl, data, function () {
            window.location.replace(baseUrl + "/cart");
        });
    }
    else {
        httpGet(baseUrl + "/cart.js", function(result) {
            var cart = JSON.parse(result);
            var dict = {};
            cart.items.forEach(function(x) {
                if(x.properties && x.properties.orderId) dict[x.properties.orderId] = x.properties._imageId;
            });

            var rows = document.getElementsByClassName("cart__row");
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].textContent.indexOf("orderId:") !== -1) {
                    var text = rows[i].textContent.replace(/\s/g,'');
                    var start = text.indexOf("orderId:") + 8;
                    var finish = text.indexOf("Remove");
                    var orderId = text.substr(start, finish-start);

                    var src = "https://process.gdtimagepro.com/ARTServer/GetImage?id=" + dict[orderId] + "&nocache=true";
                    console.debug("row " + i + ' - ' + orderId + " - " + src);

                    var imageElement = rows[i].getElementsByTagName("img");
                    imageElement[0].src = src;
                }
            }
        });
    }
}
else if( path.indexOf("thank_you") != -1 ) {
    var xhr;
    var orderId = document.getElementsByClassName("os-order-number")[0].outerText.substring(7);
    var printedId;
    var shippingInfo = {"country":Shopify.checkout.shipping_address.country,"order_id":Shopify.checkout.order_id,"first_name":Shopify.checkout.shipping_address.first_name,"last_name":Shopify.checkout.shipping_address.last_name,
        "address_1":Shopify.checkout.shipping_address.address1,"address_2":Shopify.checkout.shipping_address.address2,"city":Shopify.checkout.shipping_address.city,"state_province":Shopify.checkout.shipping_address.state_province,"zip_postal_code":Shopify.checkout.shipping_address.zip,
        "email":Shopify.checkout.email,"company":Shopify.checkout.shipping_address.company,"ship_qty":Shopify.checkout.line_items.length,"ship_method":Shopify.checkout.shipping_rate.title};

    shippingInfo = JSON.stringify(shippingInfo);

    for( var i = 0; i < Shopify.checkout.line_items.length; i++) {
        printedId = orderId*1000+i;
        xhr = new XMLHttpRequest();
        xhr.open("POST", "https://design.gdtimagepro.com/services/update_order_item_shopify.php?orderItemId=" + Shopify.checkout.line_items[i].properties.orderId + "&externalOrderId=" + printedId + "&shippingInfo=" + shippingInfo + "&confirm=true");
        xhr.send();
        console.log(xhr.responseText);
    }
}
