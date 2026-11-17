import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { datePicker, formatDate, monthGrid, stepMonth, weekdayOrder } from '../../resources/js/date-picker.js'

const strings = {
  months: {
    ethiopian: ['Meskerem', 'Tikimt', 'Hidar', 'Tahsas', 'Tir', 'Yekatit', 'Megabit', 'Miyazya', 'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'],
    ethiopianShort: ['Mes', 'Tik', 'Hid', 'Tah', 'Tir', 'Yek', 'Meg', 'Miy', 'Gin', 'Sen', 'Ham', 'Neh', 'Pag'],
    gregorian: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    gregorianShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  },
  weekdaysMin: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
  format: ':day :month :year',
  labels: {}
}

const days = (cells) => cells.filter(Boolean)

describe('monthGrid', () => {
  it('starts 1 Meskerem 2019 on a Friday column (Monday-first)', () => {
    const cells = monthGrid(2019, 1, 'ethiopian', 1)
    expect(cells.slice(0, 4)).toEqual([null, null, null, null])
    expect(cells[4]).toMatchObject({ day: 1, iso: '2026-09-11' })
    expect(days(cells)).toHaveLength(30)
    expect(cells.length % 7).toBe(0)
  })

  it('shows Pagume as a short month of 5 or 6 days', () => {
    expect(days(monthGrid(2018, 13, 'ethiopian'))).toHaveLength(5)
    expect(days(monthGrid(2019, 13, 'ethiopian'))).toHaveLength(6)
    expect(days(monthGrid(2015, 13, 'ethiopian')).at(-1).iso).toBe('2023-09-11')
  })

  it('builds Gregorian months', () => {
    const feb = monthGrid(2028, 2, 'gregorian')
    expect(days(feb)).toHaveLength(29)
    expect(days(feb)[0].iso).toBe('2028-02-01')
  })

  it('honours a Sunday week start', () => {
    // 1 Feb 2026 is a Sunday: first cell with a Sunday start.
    expect(monthGrid(2026, 2, 'gregorian', 7)[0]).toMatchObject({ day: 1 })
    expect(weekdayOrder(7)).toEqual([7, 1, 2, 3, 4, 5, 6])
  })
})

describe('stepMonth', () => {
  it('goes Nehase → Pagume → Meskerem in the Ethiopian grid', () => {
    expect(stepMonth({ year: 2018, month: 12 }, 1, 'ethiopian')).toEqual({ year: 2018, month: 13 })
    expect(stepMonth({ year: 2018, month: 13 }, 1, 'ethiopian')).toEqual({ year: 2019, month: 1 })
    expect(stepMonth({ year: 2019, month: 1 }, -1, 'ethiopian')).toEqual({ year: 2018, month: 13 })
  })

  it('wraps Gregorian years', () => {
    expect(stepMonth({ year: 2026, month: 12 }, 1, 'gregorian')).toEqual({ year: 2027, month: 1 })
    expect(stepMonth({ year: 2026, month: 1 }, -1, 'gregorian')).toEqual({ year: 2025, month: 12 })
  })
})

describe('formatDate', () => {
  it('formats in the picker calendar', () => {
    expect(formatDate('2026-09-11', 'ethiopian', strings)).toBe('1 Meskerem 2019')
    expect(formatDate('2026-09-11', 'gregorian', strings)).toBe('11 September 2026')
    expect(formatDate('2026-09-11', 'ethiopian', strings, ':month :day ቀን :year')).toBe('Meskerem 1 ቀን 2019')
    expect(formatDate('bad', 'ethiopian', strings)).toBe('')
  })
})

describe('datePicker component', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(new Date(2026, 8, 20, 10, 0)) // 10 Meskerem 2019
  })
  afterEach(() => vi.useRealTimers())

  const make = (config = {}) => {
    const c = datePicker({ strings, calendar: 'ethiopian', ...config })
    c.init()
    return c
  }

  it('opens on the selected value, or today', () => {
    const c = make({ value: '2023-09-11' })
    c.show()
    expect([c.year, c.month, c.open]).toEqual([2015, 13, true])

    const d = make()
    d.show()
    expect([d.year, d.month]).toEqual([2019, 1])
    expect(d.label).toBe('')
  })

  it('picks a day as a Gregorian string and closes', () => {
    const c = make()
    c.show()
    c.pick(c.cells.find((cell) => cell?.day === 15))
    expect(c.value).toBe('2026-09-25')
    expect(c.label).toBe('15 Meskerem 2019')
    expect(c.open).toBe(false)
  })

  it('respects min and max', () => {
    const c = make({ min: '2026-09-15', max: '2026-10-05' })
    c.show()
    const cell = (day) => c.cells.find((x) => x?.day === day)

    expect(c.isDisabled(cell(4))).toBe(true)   // 4 Meskerem = 14 Sep
    expect(c.isDisabled(cell(5))).toBe(false)  // 15 Sep
    expect(c.isDisabled(cell(26))).toBe(true)  // 6 Oct
    expect(c.canPrev).toBe(false)
    expect(c.canNext).toBe(false)

    c.pick(cell(4))
    expect(c.value).toBeNull()
  })

  it('moves through months, years and Pagume', () => {
    const c = make({ value: '2026-08-20' }) // 14 Nehase 2018
    c.show()
    expect(c.title).toBe('Nehase 2018')
    c.next()
    expect(c.title).toBe('Pagume 2018')
    c.next()
    expect(c.title).toBe('Meskerem 2019')

    c.showYears()
    expect(c.yearList[0]).toBe(2014)
    c.pageYears(-1)
    expect(c.yearList[0]).toBe(2002)
    c.pickYear(2010)
    expect(c.view).toBe('months')
    c.pickMonth(13)
    expect([c.year, c.month, c.view]).toEqual([2010, 13, 'days'])
  })

  it('selects today and clears', () => {
    const c = make()
    c.pickToday()
    expect(c.value).toBe('2026-09-20')
    c.clear()
    expect(c.value).toBeNull()
  })

  it('does not select today outside min/max, only shows it', () => {
    const c = make({ max: '2026-01-01' })
    c.show()
    c.pickToday()
    expect(c.value).toBeNull()
    expect(c.open).toBe(true)
  })

  it('swipes between months', () => {
    const c = make()
    c.show()
    c.touchStart({ changedTouches: [{ clientX: 300 }] })
    c.touchEnd({ changedTouches: [{ clientX: 100 }] })
    expect(c.month).toBe(2)
    c.touchStart({ changedTouches: [{ clientX: 100 }] })
    c.touchEnd({ changedTouches: [{ clientX: 120 }] })
    expect(c.month).toBe(2)
  })
})
