// loader
$(".loader-container").hide()

// Initialize the map and set its center location
var map = L.map("map").setView([54.5, -4], 6);

// set tilelayer for my map
L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}",{attribution:"Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012"}).addTo(map)

// creating layer group to clear map when changing from one country to another
var layerGroup = L.layerGroup().addTo(map);

var markers = L.markerClusterGroup();

var blueMarker = L.ExtraMarkers.icon({
    icon: 'fa-solid fa-city',
    markerColor: 'blue',
    shape: 'square',
    prefix: 'fa'
  });

// populate country options
var $dropdown = $("#countrySelect")

$.ajax({
    type: 'GET',       
    url:'libs/php/getCountries.php',
    dataType: 'json',
    success: function(response) {
        if(response.status.code === '200'){
            $.each(response.data, function() {
                $dropdown.append($("<option />").val(this.code).text(this.name))
            })
        }
}})

// helper function to  make asynchronous calls to PHP scripts
function asyncApiCall(url, req) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: url,
            type: 'POST',
            data:{
                query:req
            },
            beforeSend: function(){
                $(".loader-container").show()
              },
              complete: function(){
                $(".loader-container").hide()
              },
            async: true, // Making the call asynchronous
            success: function(response) {
                if(response.status.code !== '200') throw new Error(response)
                resolve(response.data)
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

async function countrySelect (code){
    // Clear existing layers
    layerGroup.clearLayers()
    markers.clearLayers()
    map.addLayer(markers);

    // Remove existing control buttons
    map.removeControl(countryInfoBtn)
    map.removeControl(weatherInfoBtn)
    map.removeControl(populationButton)
    map.removeControl(currencyInfoBtn)
    map.removeControl(wikiBtn)
    map.removeControl(holidayBtn)

    // Add new control buttons based on selected country
    countryInfoBtn.addTo(map)
    weatherInfoBtn.addTo(map)
    populationButton.addTo(map)
    currencyInfoBtn.addTo(map)
    wikiBtn.addTo(map)
    holidayBtn.addTo(map)

    console.log(code)

    let response = null
    try {
        response = await asyncApiCall('libs/php/getCountryGeoData.php', code)
        const geoLayer = L.geoJSON(response.geometry, {
            style:{
                fillColor: 'blue',
                weight: 1.5,
                opacity: .8,
                fillOpacity: 0.1
            }
        }).addTo(layerGroup)
        map.flyToBounds(geoLayer.getBounds());

        let coords = await asyncApiCall('libs/php/getCountryLocation.php', code)
        if(coords[0]===17.7134){
            coords[0] = -17.7134
        }
    } catch (error) {
        console.log(error)
    }

    var cities = await asyncApiCall("libs/php/getLocations.php", code)
    console.log(cities)
    cities.map(city=>{
          L.marker([city.lat,city.lng], {icon: blueMarker}).bindTooltip(city.name, {
            direction: "top", sticky: true
          }).addTo(markers);
    })

}

$dropdown.change(async function(){
    var countryCode = $(this).val();
    await countrySelect(countryCode)
});

async function showPosition(position) {
    var country = await asyncApiCall("libs/php/getData.php", `${position.coords.latitude},${position.coords.longitude}`)

    $("#countrySelect").val(country.code)

    countrySelect(country.code)
    $(".loader-container").hide()
}

function dennyPosition(){
    $(".loader-container").hide()
}

async function getLocation() {
    $(".loader-container").show()
    if (navigator.geolocation) {
      await navigator.geolocation.getCurrentPosition(showPosition, dennyPosition);
    } else {
      alert("Geolocation is not supported by this browser.");
      $(".loader-container").hide()
    }
}

getLocation()

// =====================================================================
// button control
// leaflet easy buttons to set various pop up modals

let countryInfoBtn = L.easyButton('fa-solid fa-info', async function(btn, map){
    var code = $('#countrySelect').val()
    var country = {}
    $(".country").empty()

    var coords = await asyncApiCall("libs/php/getCountryLocation.php", code)
    if(coords[0]===17.7134){
        coords[0] = -17.7134
    }
    // various api syncronous request and store them in country object to show it later on modal 
    var result1 = await asyncApiCall("libs/php/getData.php", `${coords[0]},${coords[1]}`)
    var result2 = await asyncApiCall("libs/php/getCountryInfo.php", code)
    var result3 = await asyncApiCall("libs/php/getNeighbours.php", code)

    country = {...result1, ...result2, ...result3}

    country.population = country.population < 1000000 ? country.population : country.population > 1000000000 ? parseFloat(country.population/1000000000).toFixed(2) + " billion" : parseFloat(country.population/1000000).toFixed(2) + " million"
    country.area = country.area < 1000 ? country.area : country.area > 1000000 ? parseFloat(country.area/1000000).toFixed(2) + " million km" : parseFloat(country.area/1000).toFixed(2) + "K km"

    $(".country").append(/*html*/`
    <div class="modal-header bg-primary bg-gradient">
        <h5 class="modal-title fs-5 text-light"  id="staticBackdropLabel">${country.name} <i class="fa-regular fa-flag"></i></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="card">
            <img src="https://flagsapi.com/${code}/flat/64.png" height="100" width="100" class="mx-auto" alt="...">
            <div class="card-body">
                <p class="card-text">Official Name</p>
                <h5 class="card-title">${country.officialName}</h5>
                <div class="box-container">
                    <div class="box-1">
                        <p class="card-text">Sub Region</p>
                        <h5 class="card-title">${country.subRegion}</h5>
                    </div>
                    <div class="box-2">
                        <p class="card-text">Region</p>
                        <h5 class="card-title">${country.region}</h5>
                    </div>
                </div>
                <div class="box-container">
                    <div class="box-1">
                        <p class="card-text">Capital</p>
                        <h5 class="card-title">${country.capital}</h5>
                    </div>
                    <div class="box-2">
                        <p class="card-text">population</p>
                        <h5 class="card-title">${country.population}</h5>
                    </div>
                </div>
                <p class="card-text">${country.neighbours.length > 1 ? "Neighbours" : "Neighbour"}</p>
                <h5 class="card-title">${country.neighbours.length > 2 ? country.neighbours.join(", ") : country.neighbours.length < 1 ? " - " : country.neighbours[0]}</h5>
                <div class="box-container">
                    <div class="box-1">
                        <p class="card-text">Area</p>
                        <h5 class="card-title">${country.area}&sup2;</h5>
                    </div>
                    <div class="box-2">
                        <p class="card-text">Coat of Arms</p>
                        <img src="${country.coatOfArms}" alt="Coat of Arms" width="70" height="70" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    `
    )
    $('.country-modal').modal('show');
} ,'Country Info')

let wikiBtn = L.easyButton('fa-brands fa-wikipedia-w', async function(btn, map){

    var code = $('#countrySelect').val()

    $(".country").empty()

    var country = await asyncApiCall("libs/php/getCountryInfo.php", code)

    var name = country.name

    var countryName = name.replace(/ /g,"_")

    var result = await asyncApiCall("libs/php/getWiki.php", countryName)

    
    $(".country").append(/*html*/`
    <div class="modal-header bg-primary bg-gradient">
        <h5 class="modal-title fs-5 text-light"  id="staticBackdropLabel">${country.name}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="card">
            <div class="card-body">
                <p>${result.extract}...</p>
                <a href="${result.url}" target="_blank" class="wiki-btn btn btn-primary">Continue Reading <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
        </div>
    </div>
    `
    )
    $('.country-modal').modal('show');
    
} ,'Wikipedia')

let weatherInfoBtn = L.easyButton('fa-solid fa-cloud-sun-rain',async function(btn, map){
    var code = $('#countrySelect').val()

    var country = await asyncApiCall("libs/php/getCountryInfo.php", code)

    $(".weather").empty()

    var coords = country.capitalGeometry

    const result = await asyncApiCall('libs/php/getWeather.php', `[${coords[0]},${coords[1]}]`)
    const weatherArray = Object.keys(result).map((key) => [key, result[key]]);

    var weatherIcon = "http://openweathermap.org/img/w/" + weatherArray[0][1].icons[0] + ".png";
    var most_frequent = weatherArray[0][1].most_frequent_weather
    

    $(".weather").append(/*html*/`
    <div class="modal-header bg-primary bg-gradient">
        <h5 class="modal-title fs-5 text-light"  id="staticBackdropLabel">Weather Forecast</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="card">
            <div class="card-body">
                <div class="container-box-1">
                    <h5><i class="fa-solid fa-location-dot fa-lg" style="color: #2495e5;"></i> ${country.capital}</h5>
                    <p class="weather-date">${weatherArray[0][0]}</p>
                    <div class="average-temp-box" >
                        <img src="${weatherIcon}" alt="" />
                        <h1>${weatherArray[0][1].avg_temperature}&deg;</h1>
                    </div>
                    <span class="text-secondary">${weatherArray[0][1].min_temperature}&deg; / ${weatherArray[0][1].max_temperature}&deg;</span>
                    <p class="freq_weather">${most_frequent}</p>
                    <hr/>
                </div>
                <div class="container-box-2"></div>
            </div>
        </div>
    </div>
    `
    )
    $('.weather-modal').modal('show');

    var divbox = $('.container-box-2')
    var forecastData = []

    weatherArray.pop()
    weatherArray.pop()

    weatherArray.forEach((item, i) => {

        var date = i=== 0 ? "today's weather" : item[0].substring(0, item[0].length-5)
        divbox.append(/*html*/`
            <p class="date">${date}</p>
            <div class="big-forecast-box">
                <div class="forecast-box swiper var-${i}">
                </div>
            </div>
        `)

        var tempArray = []
        item[1].temps.forEach((elem,i)=>{
            tempObj = {}
           tempObj.temp = elem
           tempObj.time = item[1].times[i]
           tempObj.icon = item[1].icons[i]
           tempObj.condition = item[1].weather[i]
           tempArray.push(tempObj)
        })
        forecastData.push(tempArray)
    })

    forecastData.forEach((arr, i)=>{
        var  box = $(`.var-${i}`)
        arr.forEach((data, j)=>{
            var weatherIcon = "http://openweathermap.org/img/w/" + data.icon + ".png";
            box.append(/*html*/`
                <div class="small-weather-box shadow rounded" >
                    <p>${data.time}</p>
                    <img src="${weatherIcon}" alt="" />
                    <p>${data.temp}&deg;</p>
                    <p>${data.condition}</p>
                </div>
            `)
        })
    })

} ,'Weather Info')

let populationButton = L.easyButton('fa-solid fa-chart-simple', async function(btn, map){
    var code = $('#countrySelect').val()
    $(".country").empty()

    var result = await asyncApiCall("libs/php/getNational.php", code)

    $(".country").append(/*html*/`
    <div class="modal-header bg-secondary bg-gradient">
        <h5 class="modal-title fs-5 text-light"  id="staticBackdropLabel">Population Distribution <i class="fa-solid fa-chart-simple"></i></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="card">
            <div class="card-body">
                <canvas id='pie'></canvas>
            </div>
        </div>
    </div>
    `
    )
    $('.country-modal').modal('show');
    new Chart(document.getElementById('pie'), {
        type: 'doughnut',
        data: {
          labels: ["Urban Population", "Rural Population"],
          datasets: [{
            backgroundColor: ["#4361ee",
              "#76c893"
            ],
            data: [result.urban, result.rural]
          }]
        },
        options: {
          responsive: true
        }
    })

} ,'Population Distribution')

let currencyInfoBtn = L.easyButton('fa-solid fa-sack-dollar', async function(btn, map){
    var code = $('#countrySelect').val()
    $('#amount').val(1)
    $("#exchange-label").empty()

    var coords = await asyncApiCall("libs/php/getCountryLocation.php", code)
    if(coords[0]===17.7134){
        coords[0] = -17.7134
    }
    var currencyData = await asyncApiCall("libs/php/getData.php", `${coords[0]},${coords[1]}`)
    var exchange = currencyData.currency.code

    var result = await asyncApiCall("libs/php/getCurrency.php", exchange)

    $("#exchange-label").append(currencyData.currency.name)
    $("#exchange").val(parseFloat(result.exchangeRate).toFixed(4))

    $("#amount").keyup(async function(e){
        $("#exchange").val(numeral(result.exchangeRate * $("#amount").val()).format("0,0.00"))
    })

    $('.currency-modal').modal('show');

} ,'Currency Info')

let holidayBtn = L.easyButton('fa-regular fa-calendar-days', async function(btn, map){

    var code = $('#countrySelect').val()
    $(".country").empty()

    var holidays = await asyncApiCall("libs/php/getPublicHolidays.php", code)

    $(".country").append(/*html*/`
        <div class="modal-header bg-secondary bg-gradient">
            <h5 class="modal-title fs-5 text-light" id="staticBackdropLabel">Holidays <i class="fa-regular fa-calendar-days" ></i> </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-light table-striped rounded table-hover">
                <thead>
                    <tr>
                        <th>Holiday</th>
                        <th>Date (${new Date().getFullYear()})</th>
                    </tr>
                </thead>
                <tbody class="table-data"></tbody>
            </table>
        </div>
    `)

    holidays.forEach((item, index)=>{
        var date = item.formatted_date
        $('.table-data').append(/*html*/`
        <tr>
            <td>${item.holiday_name}</td>
            <td>${date}</td>
        </tr>
        `)
    })

    $('.country-modal').modal('show');
} ,'Holiday Information')