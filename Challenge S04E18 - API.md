# Correction du Challenge

| Routes | Nom de la route | Méthodes (HTTP) |
|---|---|---|
| /api/v1/questions | api_v1_questions_list | GET |
| /api/v1/questions/{id} | api_v1_questions_read | GET |
| /api/v1/questions | api_v1_questions_new | POST |
| /api/v1/answers | api_v1_answers_new | POST |
| /api/v1/tags                   | api_v1_tags_list | GET |
| /api/v1/questions?tags=id,id,… | api_v1_questions_list | GET |


| Routes | Controller | ->méthode() |
|---|---|---|
| api_v1_questions_list | App\Controller\Api\V1\QuestionController | ->list() |
| api_v1_questions_read | App\Controller\Api\V1\QuestionController | ->read() |
| api_v1_questions_new | App\Controller\Api\V1\QuestionController | ->new() |
| api_v1_answers_new | App\Controller\Api\V1\AnswerController | ->new() |
| api_v1_tags_list | App\Controller\Api\V1\TagController | ->list() |
| api_v1_questions_list | App\Controller\Api\V1\QuestionController | ->list() |
