import { fetchPBL } from '@db/apps/pbl/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsPBL = [
    // Get PBL Details
    http.get(('/api/apps/pbl/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const expiredStatus = url.searchParams.get('expiredStatus')
      const status = url.searchParams.get('status')
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
      const fetchData = await fetchPBL(page, itemsPerPage, queryLower, expiredStatus, status);
      const dataRows = fetchData.data.rows;
      const totalExpiredCount = fetchData.data.total_expired || 0
      const totalNotExpiredCount = fetchData.data.total_not_expired || 0
      // Filter PBL
      let filteredPBL = dataRows.filter(pbl => (
        (pbl.con_req_no.toLowerCase().includes(queryLower) || 
        pbl.con_bu.toLowerCase().includes(queryLower) || 
        pbl.con_pps_no.toLowerCase().includes(queryLower) || 
        pbl.join_second_description.toLowerCase().includes(queryLower) || 
        pbl.aud_user.toLowerCase().includes(queryLower)) &&
        pbl.join_first_sts_description === (status || pbl.join_first_sts_description)
      )).reverse()

      // Sort PBL
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredPBL = filteredPBL.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredPBL = filteredPBL.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredPBL = filteredPBL.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredPBL = filteredPBL.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredPBL = filteredPBL.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalPBL = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalPBL / itemsPerPageLocal)
      
      return HttpResponse.json({
        pbl: filteredPBL,
        totalPages,
        totalPBL,
        totalExpiredCount,
        totalNotExpiredCount,
        page,
      }, { status: 200 })
    }),

    // Get Single PBL Detail
    http.get(('/api/apps/pbl/:id'), async ({ params }) => {
      const PBLId = Number(params.id)
      const pbl = db.pbl.find(e => e.id === PBLId)
      if (!pbl) {
        return HttpResponse.json({ message: 'PBL not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...pbl,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete PBL
    http.delete(('/api/apps/pbl/:id'), async ({ params }) => {
      const PBLId = Number(params.id)
      const PBLIndex = db.pbl.findIndex(e => e.id === PBLId)
      if (PBLIndex === -1) {
        return HttpResponse.json('PBL not found', { status: 404 })
      }
      else {
        db.pbl.splice(PBLIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add PBL
    http.post(('/api/apps/pbl'), async ({ request }) => {
      const pbl = await request.json()

      db.pbl.push({
        ...pbl,
        id: db.pbl.length + 1,
      })
      
      return HttpResponse.json({ body: pbl }, { status: 201 })
    }),
]
