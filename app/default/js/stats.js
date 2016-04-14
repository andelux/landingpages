function stats(url){
    (new Image()).src = url;
}
function stats_conversion( key ){
    stats(LP_BASE_URI+'stats/conversion/pixel.png?co='+key);
}
