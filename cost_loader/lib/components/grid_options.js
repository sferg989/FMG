/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    var projectGridOptions = {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : true,
        sort                : false
    }
    var RPTPeriodOptions= {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : false,
        sort                : false
    };
    return {
        projectGridOptions: projectGridOptions,
        RPTPeriodOptions  : RPTPeriodOptions
    };
})