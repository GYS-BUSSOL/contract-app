import { fetchSPKReport } from '@db/apps/spk/report/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsSPKReport = [
    // Get SPK Report Details
    http.get(('/api/apps/spk-report/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const vendor = url.searchParams.get('vendor')
      const bu = url.searchParams.get('bu')
      const startDate = url.searchParams.get('startDate')
      const endDate = url.searchParams.get('endDate')
      const spkStatus = url.searchParams.get('spkStatus')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      let queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchSPKReport(page, itemsPerPage, queryLower, vendor, bu, startDate, endDate, spkStatus);
      const dataRows = fetchData.data.rows;
      const totalActiveSPKCount = fetchData.data.total_active_spk || 0
      const totalNotActiveSPKCount = fetchData.data.total_not_active_spk || 0
      // Filter SPKReport
      let filteredSPKReport = dataRows.filter(SPKReport => (
        (SPKReport.spk_no?.toLowerCase().includes(queryLower) || 
        SPKReport.BU?.toLowerCase().includes(queryLower) || 
        SPKReport.vendor_name?.toLowerCase().includes(queryLower) || 
        SPKReport.con_req_no?.toLowerCase().includes(queryLower) || 
        SPKReport.cost_center?.toLowerCase().includes(queryLower) || 
        SPKReport.spk_jobdesc_summary?.toLowerCase().includes(queryLower) || 
        SPKReport.labor_qty?.toLowerCase().includes(queryLower) || 
        SPKReport.job_qty?.toLowerCase().includes(queryLower) || 
        SPKReport.labor_type?.toLowerCase().includes(queryLower) || 
        SPKReport.job_type?.toLowerCase().includes(queryLower) || 
        SPKReport.job_payment_type?.toLowerCase().includes(queryLower) || 
        SPKReport.job_rate?.toLowerCase().includes(queryLower) || 
        SPKReport.work_center?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort SPK Report
      if (sortByLocal) {
        if (sortByLocal === 'spk_no') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.spk_no.localeCompare(b.spk_no)
            else
              return b.spk_no.localeCompare(a.spk_no)
          })
        }
        if (sortByLocal === 'BU') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.BU.localeCompare(b.BU)
            else
              return b.BU.localeCompare(a.BU)
          })
        }
        if (sortByLocal === 'cost_center') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.cost_center.localeCompare(b.cost_center)
            else
              return b.cost_center.localeCompare(a.cost_center)
          })
        }
        if (sortByLocal === 'spk_jobdesc_summary') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.spk_jobdesc_summary.localeCompare(b.spk_jobdesc_summary)
            else
              return b.spk_jobdesc_summary.localeCompare(a.spk_jobdesc_summary)
          })
        }
        if (sortByLocal === 'labor_qty') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.labor_qty.localeCompare(b.labor_qty)
            else
              return b.labor_qty.localeCompare(a.labor_qty)
          })
        }
        if (sortByLocal === 'job_qty') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_qty.localeCompare(b.job_qty)
            else
              return b.job_qty.localeCompare(a.job_qty)
          })
        }
        if (sortByLocal === 'labor_type') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.labor_type.localeCompare(b.labor_type)
            else
              return b.labor_type.localeCompare(a.labor_type)
          })
        }
        if (sortByLocal === 'job_type') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_type.localeCompare(b.job_type)
            else
              return b.job_type.localeCompare(a.job_type)
          })
        }
        if (sortByLocal === 'job_payment_type') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_payment_type.localeCompare(b.job_payment_type)
            else
              return b.job_payment_type.localeCompare(a.job_payment_type)
          })
        }
        if (sortByLocal === 'job_rate') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_rate.localeCompare(b.job_rate)
            else
              return b.job_rate.localeCompare(a.job_rate)
          })
        }
        if (sortByLocal === 'work_center') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.work_center.localeCompare(b.work_center)
            else
              return b.work_center.localeCompare(a.work_center)
          })
        }
        if (sortByLocal === 'con_req_no') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'vendor_name') {
          filteredSPKReport = filteredSPKReport.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.vendor_name.localeCompare(b.vendor_name)
            else
              return b.vendor_name.localeCompare(a.vendor_name)
          })
        }
      }
      const totalSPKReport = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalSPKReport / itemsPerPageLocal)
      
      return HttpResponse.json({
        SPKReport: filteredSPKReport,
        totalPages,
        totalSPKReport,
        totalActiveSPKCount,
        totalNotActiveSPKCount,
        page,
      }, { status: 200 })
    }),
]
