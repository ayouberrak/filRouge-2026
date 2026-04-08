import api from './api';

const ActivityService = {
  create(data) {
    return api.post('/activities', data);
  },

  getByClassroom(classroomId) {
    return api.get(`/activities/classroom/${classroomId}`);
  },

  assignToStudents(activityId, studentIds) {
    return api.post(`/activities/${activityId}/assign`, { student_ids: studentIds });
  },

  assignToClassroom(activityId, classroomId) {
    return api.post(`/activities/${activityId}/assign-classroom`, { classroom_id: classroomId });
  }
};

export default ActivityService;
