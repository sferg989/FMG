/**
 * Created by fs11239 on 12/14/2016.
 */

$(document).ready(function() {

    var url      = "bcr_validation.php";
    function goBack() {
        window.history.back();
    }
    function checkIFDataIsLoaded(ship_code, rpt_period)
    {
        var data_check = {};
        data_check = $.ajax({
            type    : "POST",
            url     : url,
            async : false,
            data: {
                control         : "data_check",
                rpt_period      : rpt_period,
                code            : ship_code
            }
        }).responseText;
        return data_check
    }
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };
    var rpt_period = getUrlParameter('rpt_period');
    var code       = getUrlParameter('ship_code');

    $("#rpt_period_div").append(rpt_period);
    $("#title").append(code);
    $("#rpt_period_div").addClass("title_font");
    $("#title").addClass("title_font");

    $("#back_btn").click(function(){
        goBack();
    });
    $("#load_cobra_data").click(function(){

        var step        = {};
        step.code       = code;
        step.action     = "load_cobra_data";
        step.name       = "Load Cobra Data";
        step.rpt_period = rpt_period;
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).empty();
        }
        $("#status_cobra").append("<div id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
        workers     = new Worker("workers/load_cobra_data.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            console.log(e.data.id+" has completed");
            $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
        }
    });

    $("#bl_valid_check").click(function() {
        var step = {};
        var data_check;
        step.code       = code;
        step.rpt_period = rpt_period;
        step.action     = "bcr_valid_check";
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).remove();
        }

        step.name   = "Validate BCR's were implemented correctly";
        data_check = checkIFDataIsLoaded(code, rpt_period);
        var id;

        if(data_check!="")
        {
            bootbox.alert(data_check);
            return false;
        }
        var worker;
        $("#status").append("<div id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"</div>");

        workers           = new Worker("workers/bcr_valid_check.js");
        workers.onmessage = workerDone;

        workers.postMessage(step);
        function workerDone(e) {
            e.data = e.data+"";
            if(e.data!= ""){
                id = e.data.id;
                $("#img_"+id+" img").attr("src", "../images/tick.png");
            }
        }
    });


})