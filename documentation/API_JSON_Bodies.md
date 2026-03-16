# Wellnest API - Ready-to-Paste JSON Bodies

Copy and paste these JSON bodies directly into Postman **Body** tab (select **raw** → **JSON**)

---

## 1. Login - Admin
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/views/login.php

**Select form-data and use this:**
```
email: admin@my.nst.edu.ph
password: Admin123!
```

---

## 2. Login - Counselor
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/views/login.php

**Select form-data and use this:**
```
email: counselor@my.nst.edu.ph
password: Counselor123!
```

---

## 3. Submit Response - Likert Question (Option-based)
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response

**Select form-data:**
```
assessment_id: 1
question_id: 1
option_id: 5
```

---

## 4. Submit Response - Multiple Choice
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response

**Select form-data:**
```
assessment_id: 1
question_id: 2
option_id: 4
```

---

## 5. Submit Response - Open Text Question
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response

**Select form-data:**
```
assessment_id: 1
question_id: 3
response_text: I really appreciate being part of this school community. The teachers are supportive.
```

---

## 6. Submit Response - Voice Note Question
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response

**Select form-data:**
```
assessment_id: 2
question_id: 10
voice_note_path: /uploads/voice_notes/user_3_question_10.wav
```

---

## 7. Complete Assessment
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=complete_assessment

**Select form-data:**
```
assessment_id: 1
```

---

## 8. Save Game Progress
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=save_game_progress

**Select form-data:**
```
game_id: 1
score: 350
mood_before: stressed
mood_after: calm
level_reached: 5
```

---

## 9. Save Game Progress - Different Moods
**Method:** POST  
**URL:** http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=save_game_progress

**Select form-data:**
```
game_id: 1
score: 500
mood_before: anxious
mood_after: happy
level_reached: 8
```

Valid moods: `calm`, `stressed`, `anxious`, `happy`, `sad`, `neutral`, `energetic`, `tired`

---

## GET Requests (No Body Needed)

### Health Check
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=health
```

### Get Assessments
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_assessments
```

### Get Assessment Questions
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_assessment_questions&assessment_id=1
```

### Get Games
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_games
```

### Get Recommendations
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_recommendations&limit=10
```

### Get Section Students
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_section_students
```

### Get Student Details
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_student_details&student_id=4
```

### Get Notifications
```
GET http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_notifications&limit=20&unread_only=false
```

---

## Quick Copy-Paste Workflow in Postman

### Step 1: Health Check (No Auth)
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=health`
- Click **Send**

### Step 2: Login & Get Session
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/views/login.php`
- Body: **form-data**
  ```
  email: admin@my.nst.edu.ph
  password: Admin123!
  ```
- Click **Send**
- ✅ Now you have a session cookie!

### Step 3: Get Assessments
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_assessments`
- Click **Send**
- Copy assessment_id from response (should be 1, 2, 3)

### Step 4: Get Questions
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_assessment_questions&assessment_id=1`
- Click **Send**
- See question_id and option_id for next step

### Step 5: Answer Question 1
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response`
- Body: **form-data**
  ```
  assessment_id: 1
  question_id: 1
  option_id: 5
  ```
- Click **Send** ✅

### Step 6: Answer Question 2
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response`
- Body: **form-data**
  ```
  assessment_id: 1
  question_id: 2
  option_id: 4
  ```
- Click **Send** ✅

### Step 7: Answer Question 3 (Open Text)
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=submit_response`
- Body: **form-data**
  ```
  assessment_id: 1
  question_id: 3
  response_text: I really appreciate being part of this school community. The teachers are supportive and I feel welcome here.
  ```
- Click **Send** ✅

### Step 8: Complete Assessment
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=complete_assessment`
- Body: **form-data**
  ```
  assessment_id: 1
  ```
- Click **Send** ✅
- Response should show: `total_score: 9, percentage_score: 75, risk_level: low`

### Step 9: Get Recommendations
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_recommendations&limit=10`
- Click **Send** ✅

### Step 10: Get Games
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_games`
- Click **Send** ✅

### Step 11: Save Game Progress
- Method: POST
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=save_game_progress`
- Body: **form-data**
  ```
  game_id: 1
  score: 350
  mood_before: stressed
  mood_after: calm
  level_reached: 5
  ```
- Click **Send** ✅

### Step 12: Get Section Students (Counselor)
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_section_students`
- Click **Send** ✅

### Step 13: Get Student Details
- Method: GET
- URL: `http://localhost/Wellnest_Sim_Web_Application/src/api.php?action=get_student_details&student_id=4`
- Click **Send** ✅

---

## Expected Success Response Example

ALL successful responses follow this format:

```json
{
  "success": true,
  "message": "Success message here",
  "data": {
    // Response data here
  },
  "timestamp": "2026-03-15 14:35:00"
}
```

Example - Get Assessments:
```json
{
  "success": true,
  "message": "Assessments retrieved successfully",
  "data": [
    {
      "assessment_id": 1,
      "assessment_name": "School Experience",
      "description": "Evaluate your satisfaction with school...",
      "assessment_type": "school_experience",
      "total_questions": 3,
      "estimated_time": 5
    },
    {
      "assessment_id": 2,
      "assessment_name": "Mental Health Status",
      "description": "A comprehensive assessment...",
      "assessment_type": "mental_health",
      "total_questions": 24,
      "estimated_time": 15
    },
    {
      "assessment_id": 3,
      "assessment_name": "Help-Seeking Attitudes",
      "description": "Understand your experiences...",
      "assessment_type": "help_seeking",
      "total_questions": 3,
      "estimated_time": 5
    }
  ],
  "timestamp": "2026-03-15 14:35:00"
}
```

---

## Error Response Example

```json
{
  "success": false,
  "message": "Assessment not found",
  "code": 404,
  "timestamp": "2026-03-15 14:35:00"
}
```

Common errors:
- **401**: Not authenticated (need to login first)
- **403**: Permission denied (admin only, counselor only, etc)
- **404**: Resource not found (invalid ID)
- **400**: Bad request (missing required parameters)

