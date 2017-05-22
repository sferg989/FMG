/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var getGridData = function (ajax_data_object, gridDataView) {
        $.ajax({
            dataType: "json",
            url     : "lib/php/log_analysis.php",
            data    : ajax_data_object,
            success: function(data) {

                return data;
            },

        }).done(function (data){
            gridDataView(data);
            return data;
        });

    }


    return {
        getGridData     : getGridData
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
