import { fetchSPKList } from '@db/apps/spk/list/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsSPKList = [
    // Get SPKList List Details
    http.get(('/api/apps/spk-list/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const expiredStatus = url.searchParams.get('expiredStatus')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      const token = url.searchParams.get('token')
      let queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchSPKList(page, itemsPerPage, queryLower, expiredStatus, token);
      const dataRows = fetchData.data.rows;
      const totalExpiredCount = fetchData.data.total_expired || 0
      const totalNotExpiredCount = fetchData.data.total_not_expired || 0
      // Filter SPKList
      let filteredSPKList = dataRows.filter(SPKList => (
        (SPKList.join_second_spk_no?.toLowerCase().includes(queryLower) || 
        SPKList.join_third_vnd_name?.toLowerCase().includes(queryLower) || 
        SPKList.arr_con_req_no?.some(conReq => 
          conReq.con_req_no.toLowerCase().includes(queryLower)) ||
        SPKList.arr_con_pps_no?.some(conReq => 
          conReq.con_pps_no.toLowerCase().includes(queryLower)) ||
        SPKList.join_third_vnd_contact_person?.toLowerCase().includes(queryLower) || 
        SPKList.join_second_spk_jobdesc_summary?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort SPKList
      if (sortByLocal) {
        if (sortByLocal === 'join_second_spk_no') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_second_spk_no.localeCompare(b.join_second_spk_no)
            else
              return b.join_second_spk_no.localeCompare(a.join_second_spk_no)
          })
        }
        if (sortByLocal === 'join_third_vnd_name') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_third_vnd_name.localeCompare(b.join_third_vnd_name)
            else
              return b.join_third_vnd_name.localeCompare(a.join_third_vnd_name)
          })
        }
        if (sortByLocal === 'join_third_vnd_contact_person') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_third_vnd_contact_person.localeCompare(b.join_third_vnd_contact_person)
            else
              return b.join_third_vnd_contact_person.localeCompare(a.join_third_vnd_contact_person)
          })
        }
        if (sortByLocal === 'join_second_spk_jobdesc_summary') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_second_spk_jobdesc_summary.localeCompare(b.join_second_spk_jobdesc_summary)
            else
              return b.join_second_spk_jobdesc_summary.localeCompare(a.join_second_spk_jobdesc_summary)
          })
        }
        if (sortByLocal === 'join_second_spk_date') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_second_spk_date.localeCompare(b.join_second_spk_date)
            else
              return b.join_second_spk_date.localeCompare(a.join_second_spk_date)
          })
        }
        if (sortByLocal === 'join_second_spk_start_date') {
          filteredSPKList = filteredSPKList.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_second_spk_start_date.localeCompare(b.join_second_spk_start_date)
            else
              return b.join_second_spk_start_date.localeCompare(a.join_second_spk_start_date)
          })
        }
      }
      const totalSPKList = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalSPKList / itemsPerPageLocal)
      
      return HttpResponse.json({
        SPKList: filteredSPKList,
        totalPages,
        totalSPKList,
        totalExpiredCount,
        totalNotExpiredCount,
        page,
      }, { status: 200 })
    }),

    // Get Single SPKList Detail
    http.get(('/api/apps/spk-list/:id'), async ({ params }) => {
      const SPKId = Number(params.id)
      const SPKList = db.SPKList.find(e => e.id === SPKId)
      if (!SPKList) {
        return HttpResponse.json({ message: 'SPKList not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...SPKList,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete SPKList
    http.delete(('/api/apps/spk-list/:id'), async ({ params }) => {
      const SPKId = Number(params.id)
      const SPKIndex = db.SPKList.findIndex(e => e.id === SPKId)
      if (SPKIndex === -1) {
        return HttpResponse.json('SPKList not found', { status: 404 })
      }
      else {
        db.SPKList.splice(SPKIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add SPKList
    http.post(('/api/apps/spk-list'), async ({ request }) => {
      const SPKList = await request.json()

      db.SPKList.push({
        ...SPKList,
        id: db.SPKList.length + 1,
      })
      
      return HttpResponse.json({ body: SPKList }, { status: 201 })
    }),
]
