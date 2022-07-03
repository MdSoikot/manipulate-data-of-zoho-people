/* eslint-disable no-param-reassign */
export const hideWpMenu = () => {
  document.getElementsByTagName('body')[0].style.overflow = 'hidden'
  if (!Object.prototype.hasOwnProperty.call(process.env, 'PUBLIC_URL')) {
    document.getElementsByClassName('wp-toolbar')[0].style.paddingTop = 0
    document.getElementById('wpadminbar').style.display = 'none'
    document.getElementById('adminmenumain').style.display = 'none'
    document.getElementById('adminmenuback').style.display = 'none'
    document.getElementById('adminmenuwrap').style.display = 'none'
    document.getElementById('wpfooter').style.display = 'none'
    document.getElementById('wpcontent').style.marginLeft = 0
  }
}

export const showWpMenu = () => {
  document.getElementsByTagName('body')[0].style.overflow = 'auto'
  if (!Object.prototype.hasOwnProperty.call(process.env, 'PUBLIC_URL')) {
    document.getElementsByClassName('wp-toolbar')[0].style.paddingTop = '32px'
    document.getElementById('wpadminbar').style.display = 'block'
    document.getElementById('adminmenumain').style.display = 'block'
    document.getElementById('adminmenuback').style.display = 'block'
    document.getElementById('adminmenuwrap').style.display = 'block'
    document.getElementById('wpcontent').style.marginLeft = null
    document.getElementById('wpfooter').style.display = 'block'
  }
}

export const getNewId = flds => {
  let largestNumberFld = 0
  let num = 0
  for (const fld in flds) {
    if (fld !== null && fld !== undefined) {
      num = Number(fld.match(/-[0-9]+/g)?.[0]?.match(/[0-9]+/g))
      if (typeof num === 'number' && num > largestNumberFld) {
        largestNumberFld = num
      }
    }
  }
  return largestNumberFld + 1
}

export const assign = (obj, keyPath, value) => {
  const lastKeyIndex = keyPath.length - 1
  // eslint-disable-next-line no-plusplus
  for (let i = 0; i < lastKeyIndex; ++i) {
    const key = keyPath[i]
    if (!(key in obj)) {
      obj[key] = {}
    }
    obj = obj[key]
  }
  obj[keyPath[lastKeyIndex]] = value
  return value
}

export const multiAssign = (obj, assignArr) => {
  for (let i = 0; i < assignArr.length; i += 1) {
    if (assignArr[i].delProp) {
      delete obj?.[assignArr[i].cls]?.[assignArr[i].property]
      if (obj[assignArr[i]?.cls]?.constructor === Object && Object.keys(obj?.[assignArr[i]?.cls]).length === 0) {
        delete obj[assignArr[i].cls]
      }
    } else {
      assign(obj, [assignArr[i].cls, assignArr[i].property], assignArr[i].value)
    }
  }
}

export const deepCopy = (target, map = new WeakMap()) => {
  if (typeof target !== 'object' || target === null) {
    return target
  }
  const forEach = (array, iteratee) => {
    let index = -1
    const { length } = array
    // eslint-disable-next-line no-plusplus
    while (++index < length) {
      iteratee(array[index], index)
    }
    return array
  }

  const isArray = Array.isArray(target)
  const cloneTarget = isArray ? [] : {}

  if (map.get(target)) {
    return map.get(target)
  }
  map.set(target, cloneTarget)

  if (isArray) {
    forEach(target, (value, index) => {
      cloneTarget[index] = deepCopy(value, map)
    })
  } else {
    forEach(Object.keys(target), (key, index) => {
      cloneTarget[key] = deepCopy(target[key], map)
    })
  }
  return cloneTarget
}

export const sortArrOfObj = (data, sortLabel) => data.sort((a, b) => {
  if (a?.[sortLabel]?.toLowerCase() < b?.[sortLabel]?.toLowerCase()) return -1
  if (a?.[sortLabel]?.toLowerCase() > b?.[sortLabel]?.toLowerCase()) return 1
  return 0
})

export const dateTimeFormatter = (dateStr, format) => {
  const newDate = new Date(dateStr)

  if (newDate.toString() === 'Invalid Date') {
    return 'Invalid Date'
  }

  // Day
  const d = newDate.toLocaleDateString('en-US', { day: '2-digit' })
  const j = newDate.toLocaleDateString('en-US', { day: 'numeric' })
  let S = Number(j)
  if (S % 10 === 1 && S !== 11) {
    S = 'st'
  } else if (S % 10 === 2 && S !== 12) {
    S = 'nd'
  } else if (S % 10 === 3 && S !== 13) {
    S = 'rd'
  } else {
    S = 'th'
  }
  // Weekday
  const l = newDate.toLocaleDateString('en-US', { weekday: 'long' })
  const D = newDate.toLocaleDateString('en-US', { weekday: 'short' })
  // Month
  const m = newDate.toLocaleDateString('en-US', { month: '2-digit' })
  const n = newDate.toLocaleDateString('en-US', { month: 'numeric' })
  const F = newDate.toLocaleDateString('en-US', { month: 'long' })
  const M = newDate.toLocaleDateString('en-US', { month: 'short' })
  // Year
  const Y = newDate.toLocaleDateString('en-US', { year: 'numeric' })
  const y = newDate.toLocaleDateString('en-US', { year: '2-digit' })
  // Time
  const a = newDate.toLocaleTimeString('en-US', { hour12: true }).split(' ')[1].toLowerCase()
  const A = newDate.toLocaleTimeString('en-US', { hour12: true }).split(' ')[1]
  // Hour
  const g = newDate.toLocaleTimeString('en-US', { hour12: true, hour: 'numeric' }).split(' ')[0]
  const h = newDate.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit' }).split(' ')[0]
  const G = newDate.toLocaleTimeString('en-US', { hour12: false, hour: 'numeric' })
  const H = newDate.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit' })
  // Minute
  const i = newDate.toLocaleTimeString('en-US', { minute: '2-digit' })
  // Second
  const s = newDate.toLocaleTimeString('en-US', { second: '2-digit' })
  // Additional
  const T = newDate.toLocaleTimeString('en-US', { timeZoneName: 'short' }).split(' ')[2]
  const c = newDate.toISOString()
  const r = newDate.toUTCString()
  const U = newDate.valueOf()
  let formattedDate = ''
  const allFormatObj = { a, A, c, d, D, F, g, G, h, H, i, j, l, m, M, n, r, s, S, T, U, y, Y }

  const allFormatkeys = Object.keys(allFormatObj)

  for (let v = 0; v < format.length; v += 1) {
    if (format[v] === '\\') {
      v += 1
      formattedDate += format[v]
    } else {
      const formatKey = allFormatkeys.find(key => key === format[v])
      formattedDate += formatKey ? format[v].replace(formatKey, allFormatObj[formatKey]) : format[v]
    }
  }

  return formattedDate
}

export const loadScript = (src, type) => new Promise((resolve) => {
  const script = document.createElement('script')
  script.src = src
  script.onload = () => {
    resolve(true)
  }
  script.onerror = () => {
    resolve(false)
  }
  script.id = type
  document.body.appendChild(script)
})

const cipher = salt => {
  const textToChars = text => text.split('').map(c => c.charCodeAt(0))
  const byteHex = n => (`0${Number(n).toString(16)}`).substr(-2)
  // eslint-disable-next-line no-bitwise
  const applySaltToChar = code => textToChars(salt).reduce((a, b) => a ^ b, code)

  return text => text
    .split('')
    .map(textToChars)
    .map(applySaltToChar)
    .map(byteHex)
    .join('')
}

const decipher = salt => {
  const textToChars = text => text.split('').map(c => c.charCodeAt(0))
  // eslint-disable-next-line no-bitwise
  const applySaltToChar = code => textToChars(salt).reduce((a, b) => (a ^ b), code)
  return encoded => encoded
    .match(/.{1,2}/g)
    .map(hex => parseInt(hex, 16))
    .map(applySaltToChar)
    .map(charCode => String.fromCharCode(charCode))
    .join('')
}

export const bitCipher = cipher('btcd')
export const bitDecipher = decipher('btcd')

export function spreadIn4Value(value) {
  if (!value) return undefined
  const valArr = value.split(' ')
  if (valArr.length === 4) return value
  if (valArr.length === 1) return Array(4).fill(valArr[0]).join(' ')
  if (valArr.length === 2) return [valArr[0], valArr[1], valArr[0], valArr[1]].join(' ')
  if (valArr.length === 3) return [valArr[0], valArr[1], valArr[2], valArr[1]].join(' ')
  return value
}

export const checkValidEmail = email => {
  if (/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
    return true
  }
  return false
}
