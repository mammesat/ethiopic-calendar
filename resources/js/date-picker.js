/**
 * Alpine component behind <x-ethiopic::date-picker>.
 *
 * The bound value is always a Gregorian "Y-m-d" string (or null), whatever
 * calendar the grid shows. Registration:
 *
 *   import { registerDatePicker } from '../../vendor/mammesat/ethiopic-calendar/resources/js/date-picker.js'
 *   document.addEventListener('alpine:init', () => registerDatePicker(window.Alpine))
 *
 * Importing this module also registers it automatically on 'alpine:init'.
 */
import {
  daysInMonth, dayOfWeek, isoToJdn, jdnOf, jdnToIso, monthsInYear, partsOf, todayIso
} from './ethiopic.js'

// ── Pure helpers (unit-tested) ─────────────────────────────

/**
 * Cells of a month grid, row-major, padded with null to whole weeks.
 * A short month (Pagume) simply has fewer cells.
 *
 * @returns {Array<{jdn:number, iso:string, day:number}|null>}
 */
export function monthGrid (year, month, calendar, weekStart = 1) {
  const first = jdnOf(year, month, 1, calendar)
  const length = daysInMonth(year, month, calendar)
  const lead = (dayOfWeek(first) - weekStart + 7) % 7

  const cells = Array(lead).fill(null)
  for (let d = 0; d < length; d++) {
    cells.push({ jdn: first + d, iso: jdnToIso(first + d), day: d + 1 })
  }
  while (cells.length % 7 !== 0) cells.push(null)

  return cells
}

/** Weekday indexes (ISO 1–7) in grid column order. */
export function weekdayOrder (weekStart = 1) {
  return Array.from({ length: 7 }, (_, i) => ((weekStart - 1 + i) % 7) + 1)
}

/** Step a { year, month } view by delta months; Ethiopian years have 13 (Pagume). */
export function stepMonth ({ year, month }, delta, calendar) {
  const n = monthsInYear(calendar)
  const index = year * n + (month - 1) + delta
  return { year: Math.floor(index / n), month: (index % n + n) % n + 1 }
}

/** Fill a date pattern such as ":day :month :year" from translated names. */
export function formatDate (iso, calendar, strings, pattern = strings.formats?.[calendar] ?? strings.format) {
  const jdn = isoToJdn(iso)
  if (jdn === null) return ''
  const { year, month, day } = partsOf(jdn, calendar)
  const values = {
    ':dd': String(day).padStart(2, '0'),
    ':mm': String(month).padStart(2, '0'),
    ':day': String(day),
    ':month': strings.months[calendar][month - 1],
    ':year': String(year)
  }
  return pattern.replace(/:dd|:mm|:day|:month|:year/g, (k) => values[k])
}

// ── Alpine component ───────────────────────────────────────

export function datePicker (config = {}) {
  const calendar = config.calendar === 'gregorian' ? 'gregorian' : 'ethiopian'
  const strings = config.strings
  const weekStart = config.weekStart || 1
  const minJdn = isoToJdn(config.min ?? null)
  const maxJdn = isoToJdn(config.max ?? null)

  return {
    value: config.value ?? null,
    open: false,
    view: 'days',
    year: 0,
    month: 1,
    yearPageStart: 0,
    calendar,
    strings,
    touchX: null,

    init () {
      this.moveTo(this.value)
    },

    // ── Derived ──
    get label () {
      return isoToJdn(this.value) === null ? '' : formatDate(this.value, calendar, strings)
    },
    get title () {
      return `${strings.months[calendar][this.month - 1]} ${this.year}`
    },
    get cells () {
      return monthGrid(this.year, this.month, calendar, weekStart)
    },
    get weekdays () {
      return weekdayOrder(weekStart).map((d) => strings.weekdaysMin[d - 1])
    },
    get monthList () {
      const short = strings.months[calendar + 'Short']
      return Array.from({ length: monthsInYear(calendar) }, (_, i) => ({ month: i + 1, name: short[i] }))
    },
    get yearList () {
      return Array.from({ length: 12 }, (_, i) => this.yearPageStart + i)
    },
    get canPrev () {
      if (minJdn === null) return true
      const p = stepMonth(this, -1, calendar)
      return jdnOf(p.year, p.month, daysInMonth(p.year, p.month, calendar), calendar) >= minJdn
    },
    get canNext () {
      if (maxJdn === null) return true
      const n = stepMonth(this, 1, calendar)
      return jdnOf(n.year, n.month, 1, calendar) <= maxJdn
    },

    // ── Cell state ──
    isSelected (cell) { return cell !== null && cell.iso === this.value },
    isToday (cell) { return cell !== null && cell.iso === todayIso() },
    isDisabled (cell) {
      return cell === null || (minJdn !== null && cell.jdn < minJdn) || (maxJdn !== null && cell.jdn > maxJdn)
    },
    isMonthDisabled (month) {
      if (minJdn !== null && jdnOf(this.year, month, daysInMonth(this.year, month, calendar), calendar) < minJdn) return true
      if (maxJdn !== null && jdnOf(this.year, month, 1, calendar) > maxJdn) return true
      return false
    },
    isYearDisabled (year) {
      if (year < 1) return true
      const last = monthsInYear(calendar)
      if (minJdn !== null && jdnOf(year, last, daysInMonth(year, last, calendar), calendar) < minJdn) return true
      if (maxJdn !== null && jdnOf(year, 1, 1, calendar) > maxJdn) return true
      return false
    },

    // ── Navigation ──
    moveTo (iso) {
      let jdn = isoToJdn(iso) ?? isoToJdn(todayIso())
      if (minJdn !== null && jdn < minJdn) jdn = minJdn
      if (maxJdn !== null && jdn > maxJdn) jdn = maxJdn
      const p = partsOf(jdn, calendar)
      this.year = p.year
      this.month = p.month
    },
    show () {
      this.moveTo(this.value)
      this.view = 'days'
      this.open = true
    },
    close () {
      this.open = false
    },
    prev () { if (this.canPrev) Object.assign(this, stepMonth(this, -1, calendar)) },
    next () { if (this.canNext) Object.assign(this, stepMonth(this, 1, calendar)) },
    showMonths () { this.view = 'months' },
    showYears () {
      this.yearPageStart = this.year - 5
      this.view = 'years'
    },
    pickMonth (month) {
      if (this.isMonthDisabled(month)) return
      this.month = month
      this.view = 'days'
    },
    pickYear (year) {
      if (this.isYearDisabled(year)) return
      this.year = year
      this.month = Math.min(this.month, monthsInYear(calendar))
      this.view = 'months'
    },
    pageYears (delta) { this.yearPageStart += delta * 12 },

    // ── Selection ──
    pick (cell) {
      if (this.isDisabled(cell)) return
      this.value = cell.iso
      this.close()
    },
    pickToday () {
      const jdn = isoToJdn(todayIso())
      if ((minJdn !== null && jdn < minJdn) || (maxJdn !== null && jdn > maxJdn)) {
        this.moveTo(todayIso())
        return
      }
      this.value = todayIso()
      this.close()
    },
    clear () {
      this.value = null
      this.close()
    },

    // ── Swipe between months ──
    touchStart (event) { this.touchX = event.changedTouches[0].clientX },
    touchEnd (event) {
      if (this.touchX === null || this.view !== 'days') return
      const dx = event.changedTouches[0].clientX - this.touchX
      this.touchX = null
      if (Math.abs(dx) < 50) return
      dx < 0 ? this.next() : this.prev()
    }
  }
}

export function registerDatePicker (Alpine) {
  Alpine.data('ethiopicDatePicker', datePicker)
}

if (typeof document !== 'undefined') {
  document.addEventListener('alpine:init', () => registerDatePicker(window.Alpine))
}
