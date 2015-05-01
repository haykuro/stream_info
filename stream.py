#!/usr/bin/env python

# imports:
import json
import sys
import urllib2

# functions:
def getIcyStreamHeaders(response):
    print response.headers
    icy_metaint_header = response.headers.get('icy-metaint')
    if icy_metaint_header is not None:
        metaint = int(icy_metaint_header)
        read_buffer = metaint+255
        content = response.read(read_buffer)
        title = content[metaint:].split("'")[1]
        url = content[metaint:].split("'")[3]
        return {'url': url, 'title': title}
    # if no headers:
    return False

def HTTPRequest(url, headers=False):
    try:
        request = urllib2.Request(url)
        if headers:
            for key in headers:
                request.add_header(key, headers[key])
        return urllib2.urlopen(request)
    except Exception as e:
        raise e.reason
    return False

# start:
# stream_url = 'http://pub1.di.fm/di_classictrance'
stream_url = 'http://ice.somafm.com:80/defcon'

data = HTTPRequest(stream_url, {'Icy-MetaData': 1})
print json.dumps(getIcyStreamHeaders(data))