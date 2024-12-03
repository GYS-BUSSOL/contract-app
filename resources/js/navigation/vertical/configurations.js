export default [
  { heading: 'Apps Configurations' },
  {
    title: 'Configurations',
    icon: { icon: 'tabler-adjustments-horizontal' },
    children: [
      {
        title: 'Man Days Rate',
        to: 'configurations-man-days-rate',
      },
      {
        title: 'Request Management',
        to: 'configurations-request-management',
      },
      {
        title: 'Human Resources',
        to: 'configurations-hr',
      },
      {
        title: 'Area Management',
        children: [
          {
            title: 'Business Unit',
            to: 'configurations-area-management-business-unit'
          },
          {
            title: 'Cost Center',
            to: 'configurations-area-management-cost-center'
          },
          {
            title: 'Work Center',
            to: 'configurations-area-management-work-center'
          },
        ],
      },
      {
        title: 'Job Type',
        to: 'configurations-job-type',
      },
      {
        title: 'Signature Mangement',
        to: 'configurations-signature-type',
      },
    ]
  }
]
