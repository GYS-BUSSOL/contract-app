import { fetchSPKActive } from '@db/apps/spk/active/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsSPKActive = [
    // Get SPK Active Details
    http.get(('/api/apps/spk-active/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const vendor = url.searchParams.get('vendor')
      const bu = url.searchParams.get('bu')
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
      const fetchData = await fetchSPKActive(page, itemsPerPage, queryLower, vendor, bu);
      const dataRows = fetchData.data.rows;
      // Filter SPK Active
      let filteredSPKActive = dataRows.filter(SPKActive => (
        (SPKActive.spk_no?.toLowerCase().includes(queryLower) || 
        SPKActive.BU?.toLowerCase().includes(queryLower) || 
        SPKActive.vendor_name?.toLowerCase().includes(queryLower) || 
        SPKActive.con_req_no?.toLowerCase().includes(queryLower) || 
        SPKActive.cost_center?.toLowerCase().includes(queryLower) || 
        SPKActive.spk_jobdesc_summary?.toLowerCase().includes(queryLower) || 
        SPKActive.labor_qty?.toLowerCase().includes(queryLower) || 
        SPKActive.job_qty?.toLowerCase().includes(queryLower) || 
        SPKActive.labor_type?.toLowerCase().includes(queryLower) || 
        SPKActive.job_type?.toLowerCase().includes(queryLower) || 
        SPKActive.job_payment_type?.toLowerCase().includes(queryLower) || 
        SPKActive.job_rate?.toLowerCase().includes(queryLower) || 
        SPKActive.work_center?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort SPK Active
      if (sortByLocal) {
        if (sortByLocal === 'spk_no') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.spk_no.localeCompare(b.spk_no)
            else
              return b.spk_no.localeCompare(a.spk_no)
          })
        }
        if (sortByLocal === 'BU') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.BU.localeCompare(b.BU)
            else
              return b.BU.localeCompare(a.BU)
          })
        }
        if (sortByLocal === 'cost_center') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.cost_center.localeCompare(b.cost_center)
            else
              return b.cost_center.localeCompare(a.cost_center)
          })
        }
        if (sortByLocal === 'spk_jobdesc_summary') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.spk_jobdesc_summary.localeCompare(b.spk_jobdesc_summary)
            else
              return b.spk_jobdesc_summary.localeCompare(a.spk_jobdesc_summary)
          })
        }
        if (sortByLocal === 'labor_qty') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.labor_qty.localeCompare(b.labor_qty)
            else
              return b.labor_qty.localeCompare(a.labor_qty)
          })
        }
        if (sortByLocal === 'job_qty') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_qty.localeCompare(b.job_qty)
            else
              return b.job_qty.localeCompare(a.job_qty)
          })
        }
        if (sortByLocal === 'labor_type') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.labor_type.localeCompare(b.labor_type)
            else
              return b.labor_type.localeCompare(a.labor_type)
          })
        }
        if (sortByLocal === 'job_type') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_type.localeCompare(b.job_type)
            else
              return b.job_type.localeCompare(a.job_type)
          })
        }
        if (sortByLocal === 'job_payment_type') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_payment_type.localeCompare(b.job_payment_type)
            else
              return b.job_payment_type.localeCompare(a.job_payment_type)
          })
        }
        if (sortByLocal === 'job_rate') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_rate.localeCompare(b.job_rate)
            else
              return b.job_rate.localeCompare(a.job_rate)
          })
        }
        if (sortByLocal === 'work_center') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.work_center.localeCompare(b.work_center)
            else
              return b.work_center.localeCompare(a.work_center)
          })
        }
        if (sortByLocal === 'con_req_no') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'vendor_name') {
          filteredSPKActive = filteredSPKActive.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.vendor_name.localeCompare(b.vendor_name)
            else
              return b.vendor_name.localeCompare(a.vendor_name)
          })
        }
      }
      const totalSPKActive = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalSPKActive / itemsPerPageLocal)
      
      return HttpResponse.json({
        SPKActive: filteredSPKActive,
        totalPages,
        totalSPKActive,
        page,
      }, { status: 200 })
    }),

    // Get Single SPKActive Detail
    http.get(('/api/apps/spk-report/:id'), async ({ params }) => {
      const SPKId = Number(params.id)
      const SPKActive = db.SPKActive.find(e => e.id === SPKId)
      if (!SPKActive) {
        return HttpResponse.json({ message: 'SPK active not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...SPKActive,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete SPKActive
    http.delete(('/api/apps/spk-report/:id'), async ({ params }) => {
      const SPKId = Number(params.id)
      const SPKIndex = db.SPKActive.findIndex(e => e.id === SPKId)
      if (SPKIndex === -1) {
        return HttpResponse.json('SPK active not found', { status: 404 })
      }
      else {
        db.SPKActive.splice(SPKIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add SPKActive
    http.post(('/api/apps/spk-active'), async ({ request }) => {
      const SPKActive = await request.json()

      db.SPKActive.push({
        ...SPKActive,
        id: db.SPKActive.length + 1,
      })
      
      return HttpResponse.json({ body: SPKActive }, { status: 201 })
    }),
]
