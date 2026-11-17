/**
 * Gregorian <-> Ethiopian conversion through the Julian Day Number.
 * Mirrors src/Converter.php; both are tested against tests/fixtures/dates.json.
 *
 * Dates cross the boundary as plain { year, month, day } objects or Gregorian
 * "Y-m-d" strings. No Date objects are used, so the browser timezone never
 * shifts a day.
 */

export const EPOCH_JDN = 1724221

const div = (a, b) => Math.floor(a / b)

export function gregorianToJdn (year, month, day) {
  const a = div(14 - month, 12)
  const y = year + 4800 - a
  const m = month + 12 * a - 3

  return day + div(153 * m + 2, 5) + 365 * y + div(y, 4) - div(y, 100) + div(y, 400) - 32045
}

export function jdnToGregorian (jdn) {
  const a = jdn + 32044
  const b = div(4 * a + 3, 146097)
  const c = a - div(146097 * b, 4)
  const d = div(4 * c + 3, 1461)
  const e = c - div(1461 * d, 4)
  const m = div(5 * e + 2, 153)

  return {
    year: 100 * b + d - 4800 + div(m, 10),
    month: m + 3 - 12 * div(m, 10),
    day: e - div(153 * m + 2, 5) + 1
  }
}

export function ethiopicToJdn (year, month, day) {
  return EPOCH_JDN + 365 * (year - 1) + div(year, 4) + 30 * (month - 1) + day - 1
}

export function jdnToEthiopic (jdn) {
  const year = div(4 * (jdn - EPOCH_JDN) + 1463, 1461)
  const month = div(jdn - ethiopicToJdn(year, 1, 1), 30) + 1
  const day = jdn - ethiopicToJdn(year, month, 1) + 1

  return { year, month, day }
}

export const isLeapYear = (year) => year % 4 === 3

export function daysInEthiopicMonth (year, month) {
  return month <= 12 ? 30 : (isLeapYear(year) ? 6 : 5)
}

export function daysInGregorianMonth (year, month) {
  if (month === 2) return (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0)) ? 29 : 28
  return [4, 6, 9, 11].includes(month) ? 30 : 31
}

export function isValidEthiopic (year, month, day) {
  return Number.isInteger(year) && Number.isInteger(month) && Number.isInteger(day) &&
    year >= 1 && month >= 1 && month <= 13 && day >= 1 && day <= daysInEthiopicMonth(year, month)
}

/** ISO weekday: 1 = Monday … 7 = Sunday. */
export const dayOfWeek = (jdn) => (jdn % 7) + 1

export const toEthiopic = (year, month, day) => jdnToEthiopic(gregorianToJdn(year, month, day))

export const toGregorian = (year, month, day) => jdnToGregorian(ethiopicToJdn(year, month, day))

// ── Y-m-d strings ──────────────────────────────────────────

const pad = (n, width = 2) => String(n).padStart(width, '0')

export const formatIso = ({ year, month, day }) => `${pad(year, 4)}-${pad(month)}-${pad(day)}`

/** Strict "Y-m-d" → { year, month, day }, or null. Does not validate the day. */
export function parseIso (value) {
  const m = typeof value === 'string' ? /^(\d{4})-(\d{2})-(\d{2})$/.exec(value) : null
  return m ? { year: +m[1], month: +m[2], day: +m[3] } : null
}

/** Gregorian "Y-m-d" → JDN, or null when the string is not a real date. */
export function isoToJdn (value) {
  const p = parseIso(value)
  if (!p || p.month < 1 || p.month > 12 || p.day < 1 || p.day > daysInGregorianMonth(p.year, p.month)) return null
  return gregorianToJdn(p.year, p.month, p.day)
}

export const jdnToIso = (jdn) => formatIso(jdnToGregorian(jdn))

/** Today's Gregorian "Y-m-d" in the device's local time. */
export function todayIso (now = new Date()) {
  return formatIso({ year: now.getFullYear(), month: now.getMonth() + 1, day: now.getDate() })
}

// ── Calendar-neutral helpers used by the picker ────────────

/** { year, month, day } of a JDN in the given calendar ("ethiopian" | "gregorian"). */
export const partsOf = (jdn, calendar) => calendar === 'ethiopian' ? jdnToEthiopic(jdn) : jdnToGregorian(jdn)

export const jdnOf = (year, month, day, calendar) =>
  calendar === 'ethiopian' ? ethiopicToJdn(year, month, day) : gregorianToJdn(year, month, day)

export const monthsInYear = (calendar) => calendar === 'ethiopian' ? 13 : 12

export const daysInMonth = (year, month, calendar) =>
  calendar === 'ethiopian' ? daysInEthiopicMonth(year, month) : daysInGregorianMonth(year, month)
