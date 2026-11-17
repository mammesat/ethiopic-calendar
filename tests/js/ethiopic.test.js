import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'
import {
  dayOfWeek, ethiopicToJdn, formatIso, gregorianToJdn, isLeapYear, isoToJdn, isValidEthiopic,
  jdnToEthiopic, jdnToGregorian, parseIso, toEthiopic, toGregorian, todayIso, EPOCH_JDN
} from '../../resources/js/ethiopic.js'

const { rows } = JSON.parse(readFileSync(new URL('../fixtures/dates.json', import.meta.url), 'utf8'))

describe('shared fixtures', () => {
  it('cover Ethiopian years 1990 to 2060', () => {
    expect(rows[0][1]).toBe('1990-01-01')
    expect(rows.at(-1)[1]).toBe('2060-13-05')
  })

  it('match the JavaScript conversion for every date', () => {
    const failures = []

    for (const [gregorian, ethiopian, jdn, weekday] of rows) {
      const g = parseIso(gregorian)
      const e = parseIso(ethiopian)

      const ok = gregorianToJdn(g.year, g.month, g.day) === jdn &&
        ethiopicToJdn(e.year, e.month, e.day) === jdn &&
        formatIso(jdnToEthiopic(jdn)) === ethiopian &&
        formatIso(jdnToGregorian(jdn)) === gregorian &&
        dayOfWeek(jdn) === weekday

      if (!ok) failures.push(`${gregorian} / ${ethiopian}`)
    }

    expect(failures).toEqual([])
  })
})

describe('known dates', () => {
  it('11 Sep 2026 is 1 Meskerem 2019', () => {
    expect(toEthiopic(2026, 9, 11)).toEqual({ year: 2019, month: 1, day: 1 })
  })

  it('11 Sep 2023 is Pagume 6, 2015', () => {
    expect(toEthiopic(2023, 9, 11)).toEqual({ year: 2015, month: 13, day: 6 })
    expect(toGregorian(2015, 13, 6)).toEqual({ year: 2023, month: 9, day: 11 })
  })

  it('the epoch is 1 Meskerem 1', () => {
    expect(jdnToEthiopic(EPOCH_JDN)).toEqual({ year: 1, month: 1, day: 1 })
  })
})

describe('rules and parsing', () => {
  it('treats year mod 4 = 3 as leap', () => {
    expect([2011, 2015, 2019].every(isLeapYear)).toBe(true)
    expect([2016, 2018, 2020].some(isLeapYear)).toBe(false)
  })

  it('validates Ethiopian dates', () => {
    expect(isValidEthiopic(2015, 13, 6)).toBe(true)
    expect(isValidEthiopic(2016, 13, 6)).toBe(false)
    expect(isValidEthiopic(2017, 1, 31)).toBe(false)
    expect(isValidEthiopic(0, 1, 1)).toBe(false)
  })

  it('parses strict Y-m-d only', () => {
    expect(parseIso('2026-09-11')).toEqual({ year: 2026, month: 9, day: 11 })
    expect(parseIso('2026-9-11')).toBeNull()
    expect(parseIso(' 2026-09-11')).toBeNull()
    expect(parseIso(null)).toBeNull()
  })

  it('rejects impossible Gregorian dates', () => {
    expect(isoToJdn('2026-02-29')).toBeNull()
    expect(isoToJdn('2028-02-29')).not.toBeNull()
    expect(isoToJdn('2026-13-01')).toBeNull()
  })

  it('builds today from local date parts, not UTC', () => {
    expect(todayIso(new Date(2026, 8, 11, 23, 59))).toBe('2026-09-11')
  })
})
