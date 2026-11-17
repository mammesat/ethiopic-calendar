# Changelog

## Unreleased

First version, milestone 1 of the Kiressa implementation plan.

- JDN conversion (ported from filament-ethiopic-calendar) in PHP and JavaScript,
  tested against shared fixtures for every day of Ethiopian years 1990-2060
- `EthiopicDate` value object
- Anchor-day month arithmetic with the Pagume-in-Nehase rule, lease end dates
- `BillingMonth` (Nehase includes Pagume) and `FiscalYear` (1 Hamle - 30 Sene)
- `Formatter`: locale x calendar x format; Ethiopian time
- `en`, `am`, `om` translations (`om` draft)
- `<x-ethiopic::date>` and `<x-ethiopic::date-picker>` (Alpine, wire:model)
- `EthiopicDateCast`
