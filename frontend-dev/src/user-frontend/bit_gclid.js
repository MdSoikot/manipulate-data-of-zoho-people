
//gclid start------->>
function getGclid() {
    let gID = '';
    if (document.referrer) {
        const idExists = RegExp("[?&]gclid=([^&]*)").exec(document.referrer);
        if (idExists) {
            gID = idExists[1]
        }
    }

    if (gID === '' && window.location.search) {
        const idExists = RegExp("[?&]gclid=([^&]*)").exec(window.location.search);
        if (idExists) {
            gID = idExists[1]
        }
    }
    if (gID === '') {
        var c_value = document.cookie;
        var c_start = c_value.indexOf(" gclid=");
        if (c_start == -1) {
            c_start = c_value.indexOf("gclid=");
        }
        if (c_start == -1) {
            c_value = null;
        } else {
            c_start = c_value.indexOf("=", c_start) + 1;
            var c_end = c_value.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = c_value.length;
            }
            c_value = unescape(c_value.substring(c_start, c_end));
        }
        gID = c_value;
    }
    return gID;
}


document.addEventListener("DOMContentLoaded", function (event) {

    let bit_gclid = getGclid();
    if (typeof bit_gclid === 'string' && bit_gclid.length > 0) {
        let bit_gclid_elm = document.getElementsByName("gclid");
        bit_gclid_elm.forEach(elm => {
            console.log(`bit_gclid_elm`, elm);
            elm.value = bit_gclid;
        })

    }
});

var bitcf7_forms = document.getElementsByClassName('wpcf7-form');
let bit_count = bitcf7_forms.length
console.log('bit', bit_count);
if (bit_count > 0) {
    let bit_gclid = getGclid();
    while (bit_count--) {
        let form = bitcf7_forms.item(bit_count)
        if (form.elements['gclid'] && form.elements['gclid'].value !== bit_gclid) {
            form.addEventListener('submit', function (evt) {
                form.elements['gclid'].value = bit_gclid
            }, { capture: true })
        }
    }
}
    //gclid end----------------------------------->>