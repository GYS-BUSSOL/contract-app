import { addPPS, fetchApprovalLvl2Completed, fetchApprovalLvl2OnGoing } from '@db/apps/approval/lvl2/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsApproval2 = [
  
    // Get Approval Level 2 Details

    http.get(('/api/apps/approval-lvl2-ongoing/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const priority = url.searchParams.get('priority')
      const expiredStatus = url.searchParams.get('expiredStatus')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      const queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchApprovalLvl2OnGoing(page, itemsPerPage, queryLower, priority, expiredStatus);
      const dataRows = fetchData.data.rows;
      const totalPriorityCount = fetchData.data.total_priority || 0
      const totalNotPriorityCount = fetchData.data.total_not_priority || 0
      // Filter Approval Level 2 On Going
      let filteredApprovalLvl2 = dataRows.filter(approvalLvl2 => (
        (approvalLvl2.con_req_no.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_bu.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_pps_no.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_second_description.toLowerCase().includes(queryLower) || 
        approvalLvl2.aud_user.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_first_sts_description.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_fourth_spk_no.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_priority_id.toLowerCase().includes(queryLower)) && 
        approvalLvl2.con_priority_id === (priority || approvalLvl2.con_priority_id)
      )).reverse()

      // Sort Approval Level 2 On Going
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'con_priority_id') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_priority_id.localeCompare(b.con_priority_id)
            else
              return b.con_priority_id.localeCompare(a.con_priority_id)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalApprovalLvl2 = fetchData.data.total_record

      // total pages
      const totalPages = Math.ceil(totalApprovalLvl2 / itemsPerPageLocal)
      
      return HttpResponse.json({
        approvalLvl2: filteredApprovalLvl2,
        totalPages,
        totalApprovalLvl2,
        totalPriorityCount,
        totalNotPriorityCount,
        page,
      }, { status: 200 })
    }),

    http.get(('/api/apps/approval-lvl2-completed/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('qCompleted')
      const expiredStatus = url.searchParams.get('expiredStatus')
      const priority = url.searchParams.get('priorityCompleted')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      const queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchApprovalLvl2Completed(page, itemsPerPage, queryLower, priority, expiredStatus);
      const dataRows = fetchData.data.rows;
      const totalExpiredCount = fetchData.data.total_expired || 0
      const totalNotExpiredCount = fetchData.data.total_not_expired || 0

      // Filter Approval Level 2 Completed
      let filteredApprovalLvl2 = dataRows.filter(approvalLvl2 => (
        (approvalLvl2.con_req_no?.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_bu?.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_pps_no?.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_second_description?.toLowerCase().includes(queryLower) || 
        approvalLvl2.aud_user?.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_third_aud_user?.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_first_sts_description?.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_fifth_spk_renewal_box?.toLowerCase().includes(queryLower) || 
        approvalLvl2.con_comment_jobtarget?.toLowerCase().includes(queryLower) || 
        approvalLvl2.join_fifth_spk_no?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Approval Level 2 Completed
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'join_fifth_spk_no') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_fifth_spk_no.localeCompare(b.join_fifth_spk_no)
            else
              return b.join_fifth_spk_no.localeCompare(a.join_fifth_spk_no)
          })
        }
        if (sortByLocal === 'con_comment_jobtarget') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_comment_jobtarget.localeCompare(b.con_comment_jobtarget)
            else
              return b.con_comment_jobtarget.localeCompare(a.con_comment_jobtarget)
          })
        }
        if (sortByLocal === 'join_fifth_spk_renewal_box') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_fifth_spk_renewal_box.localeCompare(b.join_fifth_spk_renewal_box)
            else
              return b.join_fifth_spk_renewal_box.localeCompare(a.join_fifth_spk_renewal_box)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
        if (sortByLocal === 'join_second_description') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_second_description.localeCompare(b.join_second_description)
            else
              return b.join_second_description.localeCompare(a.join_second_description)
          })
        }
        if (sortByLocal === 'aud_user') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.aud_user.localeCompare(b.aud_user)
            else
              return b.aud_user.localeCompare(a.aud_user)
          })
        }
        if (sortByLocal === 'join_third_aud_user') {
          filteredApprovalLvl2 = filteredApprovalLvl2.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_third_aud_user.localeCompare(b.join_third_aud_user)
            else
              return b.join_third_aud_user.localeCompare(a.join_third_aud_user)
          })
        }
      }
      const totalApprovalLvl2Completed = fetchData.data.total_record

      // total pages
      const totalPages = Math.ceil(totalApprovalLvl2Completed / itemsPerPageLocal)
      
      return HttpResponse.json({
        approvalLvl2Completed: filteredApprovalLvl2,
        totalPages,
        totalApprovalLvl2Completed,
        totalExpiredCount,
        totalNotExpiredCount,
        page,
      }, { status: 200 })
    }),

    // Get Single pps Detail
    http.get(('/api/apps/pps/:id'), async ({ params }) => {
      const PPSId = Number(params.id)
      const pps = db.pps.find(e => e.id === PPSId)
      if (!pps) {
        return HttpResponse.json({ message: 'PPS not found' }, { status: 404 })
      }
      else {
        return HttpResponse.json({
          ...pps,
          ...{
            taskDone: 1230,
            projectDone: 568,
            taxId: 'Tax-8894',
            language: 'English',
          },
        }, { status: 200 })
      }
    }),

    // Delete PPS
    http.delete(('/api/apps/pps/:id'), async ({ params }) => {
      const PPSId = Number(params.id)
      const PPSIndex = db.pps.findIndex(e => e.id === PPSId)
      if (PPSIndex === -1) {
        return HttpResponse.json('PPS not found', { status: 404 })
      }
      else {
        db.pps.splice(PPSIndex, 1)
        
        return new HttpResponse(null, {
          status: 204,
        })
      }
    }),

    // 👉 Add PPS
    http.post(('/api/apps/pps/add'), async ({ request }) => {
      // Fetch Data
      const ppsFormData = await request.formData();
      console.log({ppsFormData});
      
      const ppsData = {};
      for (const [key, value] of ppsFormData.entries()) {
          ppsData[key] = value;
      }

      if (ppsData.shift_checklist) {
          ppsData.shift_checklist = JSON.parse(ppsData.shift_checklist);
      }
      if (ppsData.duration) {
          ppsData.duration = JSON.parse(ppsData.duration);
      }
      console.log({ppsData});
      
      const fetchData = await addPPS(ppsData);

      return HttpResponse.json({ body: fetchData }, { status: 201 })
    }),
]
