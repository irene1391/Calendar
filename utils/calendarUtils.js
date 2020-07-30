//this file used to show the date of the calendar 
const debug = console.error

const hostDomain = 'http://3.89.103.49/~irene/mod5'
const endpoints = {
  login: `${hostDomain}/login.php`,
  logout: `${hostDomain}/logout.php`,
  signup: `${hostDomain}/signup.php`,
  addEvent: `${hostDomain}/addevent.php`,
  editEvent: `${hostDomain}/editevent.php`,
  deleteEvent: `${hostDomain}/deleteevent.php`,
  getEvents: `${hostDomain}/getevents.php`,
  getEvent: `${hostDomain}/getevent.php`
}

const store = {
  user: {
    isLogin: false,
    token: '',
    username: ''
  },
  calendar: {
    year: 0,
    month: 0
  }//session
}

const storeUtils = {
  getStore: key => store[key],
  setStore: (key, newState, trigger = () => true) => {//trigger did not use yet
    store[key] = Object.assign({}, store[key] || {}, newState)
  }
}

const requestUtils = {
  get: (url, queryParams, callback) => fetch(`${url}${queryParams||''}`, { method: 'GET', credentials: 'include', mode: 'cors' }).then(res => res.json()).then(callback).catch(debug),//for the convenience of local debug, there was no local cookie
  post: (url, data, callback) => fetch(url, { method: 'POST', credentials: 'include', mode: 'cors', body: JSON.stringify(data||{})}).then(res => res.json()).then(callback).catch(debug)
}

const stringUtils = {
  stringToObject: string => {try {return JSON.parse(string)} catch (e) {debug(e)}},
  objectToString: object => {try {return JSON.stringify(object)} catch (e) {debug(e)}},
  objectToQuery: object => `?${Object.entries(object).map(([a,b]) => `${a}=${encodeURIComponent(b)}`).join('&')}`,
  numberToString: (number, fillToLen) => {
    let result = `${number}`

    if (result.length < fillToLen) {
      result = `${'0'.repeat(fillToLen - result.length)}${result}`
    }

    return result
  },
  stringToNumber: string => parseInt(string),
  coordObjToCoord: coordObj => `${coordObj.lat},${coordObj.lng}`
}
//The function below used to caculate the accurate date of the calendar
const timeUtils = {
  getWeekday: (year, month, day) => (new Date(year, month-1, day)).getDay(),
  getWeekdayName: (year, month, day) => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][(new Date(year, month-1, day)).getDay()],
  getMonthName: (year, month, day) => ['January','February','March','April','May','June','July','August','September','October','November','December'][(new Date(year, month-1, day)).getMonth()],
  getStartDayOfWeek: (year, month, day) => {const d=new Date(year, month-1, day),f=new Date(d.setDate(d.getDate() - d.getDay()));return { year: f.getFullYear(), month: f.getMonth() + 1, day: f.getDate()}},
  getEndDayOfWeek: (year, month, day) => {const d=new Date(year, month-1, day);d.setDate(d.getDate() - d.getDay());const l=new Date(d.setDate(d.getDate()+6));return { year: l.getFullYear(), month: l.getMonth() + 1, day: l.getDate()}},
  getTimestamp: () => `${new Date().getTime()}`,
  getFebDayCountOfYear: year => {
    if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) return 29

    return 28
  },
  getWeeksListOfYearMonth: (year, month) => {
    const monthDayCount = [31, timeUtils.getFebDayCountOfYear(year), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
    const currentMonthDayCount = monthDayCount[month - 1]
    
    const startDate = timeUtils.getStartDayOfWeek(year, month, 1)
    const endDate = timeUtils.getEndDayOfWeek(year, month, currentMonthDayCount)
    const daylist = []

    if (startDate.month !== month) {
      for (let i = 0; i <= monthDayCount[startDate.month - 1] - startDate.day; i++) {
        daylist.push({
          year: startDate.year,
          month: startDate.month,
          day: startDate.day + i
        })
      }
    }
    for (let i = 1; i <= currentMonthDayCount; i++) {
      daylist.push({ year, month, day: i })
    }
    if (endDate.month !== month) {
      for (let i = 1; i <= endDate.day; i++) {
        daylist.push({
          year: endDate.year,
          month: endDate.month,
          day: i
        })
      }
    }

    const result = []

    for (let i = 0, j=daylist.length; i < j; i += 7) {
        result.push(daylist.slice(i, i + 7))
    }

    return result
  },
  getDateNextNDays: ({ year, month, day }, n) => {const d = new Date(new Date(year, month-1, day).getTime() + n * 24 * 60 * 60 * 1000);return { year: d.getFullYear(), month: d.getMonth() + 1, day: d.getDate()}},
  getRecurringDate: (year, month, day, rp, cadence) => {
    const acc = []
    let d = { year, month, day };

    [...Array(rp)].map(_ => {acc.push(Object.assign({}, d));d = timeUtils.getDateNextNDays(d, cadence)})

    return acc
  }
}

const elementUtils = {
  getElement: (cssQuery, dom = document) => dom.querySelector(cssQuery),//if did not pass dom, it will be document
  getElements: (cssQuery, dom = document) => [...dom.querySelectorAll(cssQuery)],//tip: get node list, more than array. the...will spread operator, then can use the array operator
  removeClassFromElement: (classString, element) => {
    if (element.classList.contains(classString)) element.classList.remove(classString)
  },
  addClassToElement: (classString, element) => {
    if (!element.classList.contains(classString)) element.classList.add(classString)
  },

//innerHtml + innerText
  getTextInElement: element => element.textContent,
  setTextInElement: (textContent, element) => element.textContent = textContent,
  setInnerHtml: (htmlString, element) => element.innerHTML = htmlString,
  removeElement: element => element.parentNode.removeChild(element),//that is so briliant, gonna use it often, remember
  appendToElement: (htmlString, element) => element.insertAdjacentHTML('beforeend', htmlString)
}
