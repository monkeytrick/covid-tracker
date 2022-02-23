const regionCodes = {
    eu: ['va', 'ch', 'ad', 'ee', 'is', 'am', 'al', 'cz', 'ge', 'at', 'ie', 'gi', 'gr', 'nl', 'pt', 'no', 'lv', 'lt', 'lu', 'es', 'it', 'ro', 'pl', 'be', 'fr', 'bg', 'dk', 'hr', 'de', 'hu', 'ba', 'fi', 'by', 'fo', 'mc', 'cy', 'mk', 'sk', 'mt', 'si', 'sm', 'se', 'gb'],
    afr: ['gw', 'zm', 'ci', 'eh', 'gq', 'eg', 'cg', 'cf', 'ao', 'ga', 'et', 'gn', 'gm', 'zw', 'cv', 'gh', 'rw', 'tz', 'cm', 'na', 'ne', 'ng', 'tn', 'lr', 'ls', 'tg', 'td', 'er', 'ly', 'bf', 'dj', 'sl', 'bi', 'bj', 'za', 'bw', 'dz', 'sz', 'mg', 'ma', 'ke', 'ml', 'km', 'st', 'mu', 'mw', 'so', 'sn', 'mr', 'sc', 'ug', 'sd', 'mz'],
    ocea: ['ck', 'pw', 'pg', 'tv', 'ki', 'mh', 'nu', 'to', 'nz', 'au', 'vu', 'sb', 'ws', 'fj', 'fm'],
    asia: ['mn', 'cn', 'af', 'am', 'vn', 'ge', 'in', 'az', 'id', 'ru', 'la', 'tw', 'tr', 'lk', 'tm', 'tj', 'pg', 'th', 'np', 'pk', 'ph', 'bd', 'ua', 'bn', 'jp', 'bt', 'hk', 'kg', 'uz', 'mm', 'sg', 'mo', 'kh', 'kr', 'mv', 'kz', 'my'],
    nAm: ['gt', 'ag', 'vg', 'ai', 'vi', 'ca', 'gd', 'aw', 'cr', 'cu', 'pr', 'ni', 'tt', 'gp', 'pa', 'do', 'dm', 'bb', 'ht', 'jm', 'hn', 'bs', 'bz', 'sx', 'sv', 'us', 'mq', 'ms', 'ky', 'mx'],
    sAm: ['gd', 'py', 'co', 've', 'cl', 'sr', 'bo', 'ec', 'gf', 'ar', 'gy', 'br', 'pe', 'uy', 'fk'],
    midE: ['om', 'lb', 'iq', 'ye', 'ir', 'bh', 'sy', 'qa', 'jo', 'kw', 'il', 'ae', 'sa']
}

let regionCoords = {
    eu: [-301.0804321728722, -73.72234608129008],
    afr: [-356.4740181787001, -223.27216600926081],
    ocea: [-603.7900874635568, -323.53798662322066],
    asia: [-448.6880466472318, -71.91390842051081],
    nAm: [0, -98.47024524095349],
    sAm: [0, -236.37454981992795],
    midE: [-382.93260161207365, -165.97581889898817],
    world: [-1.27061759806512e-13, 32.35294117647055]
}


//Sidebar elements
const regionClick = document.querySelector(".nav-pills")

//Create map 
jQuery(document).ready(function () {
    jQuery('#vmap').vectorMap({
      map: 'world_en',
      backgroundColor: 'lightblue',
      color: '#ffffff',
      hoverOpacity: 0.7,
      selectedColor: '#666666',
      enableZoom: true,
      showTooltip: true,
      scaleColors: ['#C8EEFF', '#006491'],
      values: allData,
      normalizeFunction: 'polynomial',
      onRegionClick: function (element, code, region) {
        getCountData(element, code, region)                   
      }          
    });
});
jQuery('#vmap').on('regionClick.jqvmap')

//Get data for individual country 
function getCountData(event, code, region) {

    let ctyCode = code.toUpperCase()

    try {
        fetch('' + '?country=' + ctyCode, 
                                    {method: 'GET', headers: {'Content-Type':'application/json'}})
        .then(response => response.json())
        .then(data => {
            //Filter for null values
            let dataFilter = new Map;    
            Object.entries(data['country-data']).forEach(([key, val]) => {
                if(val == null) {
                    val = "0"
                    dataFilter.set(key, val)
                }
                else {
                    dataFilter.set(key, val)
                }
            })
            document.getElementById('count-name').innerText = data.country
            document.getElementById('new').innerText = dataFilter.get('new').toLocaleString("en-us")
            document.getElementById('active').innerText = dataFilter.get('active').toLocaleString("en-us")
            document.getElementById('critical').innerText = dataFilter.get('critical').toLocaleString("en-us")
            document.getElementById('total').innerText = dataFilter.get('total').toLocaleString("en-us")
        })                

    } catch (e) {             
      console.log(e)              
    }    
}  
  
//Highlight countries by region 
function getRegionData(rgnCde) {

    let countryEle = document.querySelectorAll('path')

    if(rgnCde == "world"){
        //Loop through all elements and set to original colour
        countryEle.forEach(elem => {
            elem.setAttribute('fill', elem.getAttribute('original'))
        })
    }
    else {
        //Get array from regionCodes object based on user selection
        let passValue = regionCodes[rgnCde]

        //Have to use standard for loop as forEach cannot use break statement. 
        // for in and for as would need elems to be further modified
        countryEle.forEach(elem => {
            for(i=0; i <= passValue.length; i++) {
                //If elem Id matches array value, set color
                if(passValue[i] == elem.getAttribute('id').substring(8)){
                    elem.setAttribute('fill', elem.getAttribute('original'))
                    // break loop or will reset previously set colors in same array upon second, etc. calls
                    break;
                }
                else{
                    //Fill color as white if not in region
                    elem.setAttribute('fill', 'white')
                    //Add listener so data can still be requested with function
                    elem.addEventListener('click', function(){
                        elem.setAttribute('fill', elem.getAttribute('original'))
                    })
                } 
            }
        })
        //Prevent default events
        jQuery('#vmap').bind('regionMouseOver.jqvmap', function(event, code, region){event.preventDefault()})
        jQuery('#vmap').bind('regionMouseOut.jqvmap', function(event, code, region){event.preventDefault()})
        jQuery('#vmap').bind('regionClick.jqvmap', function(event, code, region){event.preventDefault()})
    }      
}

//Sidebar options - highlight on click
regionClick.addEventListener('click', (e) => {
    document.querySelector('.active').classList.remove('active')
    e.target.className += " active"
    //Call function, pass region code
    getRegionData(e.target.dataset.region)
})
