export default [
  { heading: 'Apps' },
  {
    title: 'PPS',
    icon: { icon: 'tabler-file-stack' },
    to: 'apps-pps',
  },
  {
    title: 'Renewal',
    icon: { icon: 'tabler-loader-3' },
    to: 'apps-renewal',
  },
  {
    title: 'Vendor Assigment',
    icon: { icon: 'tabler-user-plus' },
    to: 'apps-vendor-assigment',
  },
  {
    title: 'Approval',
    icon: { icon: 'tabler-circle-dashed-check' },
    children: [
      { title: 'Approval Lv 1', to: 'apps-approval-lvl1' },
      { title: 'Approval Lv 2', to: 'apps-approval-lvl2' }
    ],
  },
  {
    title: 'PBL',
    icon: { icon: 'tabler-list-letters' },
    to: 'apps-pbl',
  },
  {
    title: 'SPK',
    icon: { icon: 'tabler-checkup-list' },
    children: [
      { title: 'Data List', to: 'apps-spk-list' },
      { title: 'Data Report', to: 'apps-spk-report' },
      { title: 'Report Active', to: 'apps-spk-active' }
    ],
  },
  {
    title: 'Reviewer',
    icon: { icon: 'tabler-message-report' },
    to: 'apps-reviewer',
  },
  {
    title: 'Budget BU',
    icon: { icon: 'tabler-businessplan' },
    to: 'apps-budget-bu',
  },
]
