/* eslint-disable no-undef */
export default async function bitsFetch(data, action, options = {}, queryParam = null) {
  const uri = new URL(typeof bitwelzp === 'undefined' ? '' : bitwelzp.ajaxURL)
  uri.searchParams.append('action', `bitwelzp_${action}`)
  uri.searchParams.append('_ajax_nonce', typeof bitwelzp === 'undefined' ? '' : bitwelzp.nonce)

  // append query params in url
  if (queryParam) {
    for (const key in queryParam) {
      if (key) {
        uri.searchParams.append(key, queryParam[key])
      }
    }
  }

  const bodyOptions = {
    method: options.method || 'POST',
    headers: {
      //  'Content-Type': contentType === null ? 'application/x-www-form-urlencoded' : contentType,
    },
  }
  if (Object.keys(data).length) {
    bodyOptions.body = data instanceof FormData ? data : JSON.stringify(data)
  }

  const response = await fetch(uri, bodyOptions)
    .then(res => res.json())
  return response
}

