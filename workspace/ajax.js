$(document).ready(function () {
    function GET(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param); // Returns the value of the parameter or null if not found
    }

    function GET_ALL() {
        const urlParams = new URLSearchParams(window.location.search);
        const params = {};
        urlParams.forEach((value, key) => {
            params[key] = value;
        });
        return params;
    }

    // -- Ajax --------

    function ajax(path, time) {
        console.log(path);
        $.ajax({
            url: path, // Path to PHP script
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {

                    console.log("Data refresh", response.data);

                    if (response.data && typeof response.data === 'object') {
                        Object.entries(response.data).forEach(([key, value]) => {
                            let element = `#${key}`;
                            $(element).html(value);
                        });
                    } else {
                        console.log('Invalid data format:', response.data);
                    }

                } else {
                    $('.generated').html('<div style="margin: auto; text-align: center;">Error: ' + response.message + '</div>');
                    console.log(response.message);
                }
            },
            error: function (response) {
                $('.generated').html('<div style="margin: auto; text-align: center;">Error: Failed to fetch data</div>');
                console.log(response);
            },
            complete: function () {
                // Re-run the function after X seconds
                timeout = time * 1000;

                setTimeout(() => {
                    ajax(path, time);
                }, timeout);
            }
        });
    }

    // -- Ajax Call ---

    function ajaxProcess(timeouts, func, detail) {
        let url = "";

        timeouts.forEach(time => {
            if (detail) {
                url = '../ajax-'+time+'.php?func='+func;
            } else {
                url = 'ajax-'+time+'.php?func='+func;
            }
            
            ajax(url, time);
        });
    }


    function fetchData() {
        let get = GET("profile");
        console.log(get);
        if (get) {
            let detail = false;
            let timeouts = [10];
            
            let deviceGet = GET("device");
            console.log(deviceGet);
            if (deviceGet) {
                detail = true;
                timeouts = [5, 10];
            }

            let func = "device";
            ajaxProcess(timeouts, func, detail);
        }
    }

    fetchData();
});