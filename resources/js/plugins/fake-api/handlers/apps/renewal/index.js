import { fetchRenewal } from '@db/apps/renewal/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsRenewal = [
    // Get Renewal Assigment Details
    http.get(('/api/apps/renewal/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const priority = url.searchParams.get('priority')
      const status = url.searchParams.get('status')
      const expiredStatus = url.searchParams.get('expiredStatus')
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
      const fetchData = await fetchRenewal(page, itemsPerPage, queryLower, priority, status, expiredStatus);
      const dataRows = fetchData.data.rows;
      const totalPriorityCount = fetchData.data.total_priority || 0
      const totalNotPriorityCount = fetchData.data.total_not_priority || 0
      // Filter Renewal
      let filteredRenewals = dataRows.filter(renewal => (
        (renewal.con_req_no.toLowerCase().includes(queryLower) || 
        renewal.con_bu.toLowerCase().includes(queryLower) || 
        renewal.con_pps_no.toLowerCase().includes(queryLower) || 
        renewal.join_second_description.toLowerCase().includes(queryLower) || 
        renewal.aud_user.toLowerCase().includes(queryLower) || 
        renewal.join_first_sts_description.toLowerCase().includes(queryLower) || 
        renewal.con_priority_id.toLowerCase().includes(queryLower)) && 
        renewal.con_priority_id === (priority || renewal.con_priority_id) && 
        renewal.join_first_sts_description === (status || renewal.join_first_sts_description)
      )).reverse()

      // Sort Renewals
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'con_priority_id') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_priority_id.localeCompare(b.con_priority_id)
            else
              return b.con_priority_id.localeCompare(a.con_priority_id)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredRenewals = filteredRenewals.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalRenewals = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalRenewals / itemsPerPageLocal)
      
      return HttpResponse.json({
        renewals: filteredRenewals,
        totalPages,
        totalRenewals,
        totalPriorityCount,
        totalNotPriorityCount,
        page,
      }, { status: 200 })
    }),

    // Get Single Renewal Detail
    http.get(('/api/apps/renewal/:id'), async ({ params }) => {
      const renewalId = Number(params.id)
      const renewal = db.renewals.find(e => e.id === renewalId)
      if (!renewal) {
        return HttpResponse.json({ message: 'Renewal not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...renewal,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete Renewal
    http.delete(('/api/apps/renewal/:id'), async ({ params }) => {
      const renewalId = Number(params.id)
      const renewalIndex = db.renewals.findIndex(e => e.id === renewalId)
      if (renewalIndex === -1) {
        return HttpResponse.json('Renewal not found', { status: 404 })
      }
      else {
        db.renewals.splice(renewalIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add Renewal
    http.post(('/api/apps/renewal'), async ({ request }) => {
      const renewal = await request.json()

      db.renewals.push({
        ...renewal,
        id: db.renewals.length + 1,
      })
      
      return HttpResponse.json({ body: renewal }, { status: 201 })
    }),
]
