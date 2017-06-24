import pyowm
import time

# create client
owm = pyowm.OWM('a766f243dff96643d5abda1111322204')

lat = 36.8065
lon = 127.1522


# iterate results
o1 = owm.weather_at_coords(lat, lon)
loc = o1.get_location()
weat = o1.get_weather()

print "< Current Weather >"
print 'location:', str(loc.get_name())
print 'weather:', weat.get_status(), "at", weat.get_reference_time('iso')
print 'temperature: ', weat.get_temperature(unit='celsius')['temp'], 'degree'

wc = weat.get_weather_code()
print wc
print weat.get_weather_icon_name()
if wc/100 == 2:
  print 'Thunderstorm'
elif wc/100 == 3:
  print 'Drizzle'
elif wc/100 == 5:
  print 'Rain'
elif wc/100 == 7:
  print 'Atmosphere'
elif wc/100 == 8:
  print 'Clouds'
elif wc/100 == 9:
  print 'Extreme'
print ''

fc1 = owm.daily_forecast(str(loc.get_name()))
fore = fc1.get_forecast()
print "< Forecast Weather on", time.strftime("%a, %d %b %Y %H:%M:%S", time.localtime(fore.get_reception_time())), ">"
for item in fore:
    lt = time.localtime(item.get_reference_time())
    print time.strftime("%a, %d %b %H:%M", lt), item.get_status(), item.get_weather_code()
