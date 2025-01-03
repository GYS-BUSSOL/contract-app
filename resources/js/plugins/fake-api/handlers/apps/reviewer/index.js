import { fetchReviewer } from '@db/apps/reviewer/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerAppsReviewer = [
  
    // Get Reviewer Assigment Details
    http.get(('/api/apps/reviewer/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const status = url.searchParams.get('status')
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
      const fetchData = await fetchReviewer(page, itemsPerPage, queryLower, expiredStatus, status);
      const dataRows = fetchData.data.rows;
      const totalExpiredCount = fetchData.data.total_expired || 0
      const totalNotExpiredCount = fetchData.data.total_not_expired || 0
      // Filter Reviewer
      let filteredReviewers = dataRows.filter(reviewer => (
        (reviewer.con_req_no?.toLowerCase().includes(queryLower) || 
        reviewer.con_bu?.toLowerCase().includes(queryLower) || 
        reviewer.con_pps_no?.toLowerCase().includes(queryLower) || 
        reviewer.join_second_description?.toLowerCase().includes(queryLower) || 
        reviewer.join_third_vnd_name?.toLowerCase().includes(queryLower) || 
        reviewer.join_fifth_spk_no?.toLowerCase().includes(queryLower) || 
        reviewer.con_comment_bu?.toLowerCase().includes(queryLower) || 
        reviewer.aud_user?.toLowerCase().includes(queryLower)) && 
        reviewer.join_first_sts_description === (status || reviewer.join_first_sts_description)
      )).reverse()

      // Sort Reviewer
      if (sortByLocal) {
        if (sortByLocal === 'con_req_no') {
          filteredReviewers = filteredReviewers.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_req_no.localeCompare(b.con_req_no)
            else
              return b.con_req_no.localeCompare(a.con_req_no)
          })
        }
        if (sortByLocal === 'request_date') {
          filteredReviewers = filteredReviewers.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.request_date.localeCompare(b.request_date)
            else
              return b.request_date.localeCompare(a.request_date)
          })
        }
        if (sortByLocal === 'join_first_sts_description') {
          filteredReviewers = filteredReviewers.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.join_first_sts_description.localeCompare(b.join_first_sts_description)
            else
              return b.join_first_sts_description.localeCompare(a.join_first_sts_description)
          })
        }
        if (sortByLocal === 'con_bu') {
          filteredReviewers = filteredReviewers.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_bu.localeCompare(b.con_bu)
            else
              return b.con_bu.localeCompare(a.con_bu)
          })
        }
        if (sortByLocal === 'con_pps_no') {
          filteredReviewers = filteredReviewers.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.con_pps_no.localeCompare(b.con_pps_no)
            else
              return b.con_pps_no.localeCompare(a.con_pps_no)
          })
        }
      }
      const totalReviewers = fetchData.data.total_record
      // total pages
      const totalPages = Math.ceil(totalReviewers / itemsPerPageLocal)
      
      return HttpResponse.json({
        reviewers: filteredReviewers,
        totalPages,
        totalReviewers,
        totalExpiredCount,
        totalNotExpiredCount,
        page,
      }, { status: 200 })
    }),
]
