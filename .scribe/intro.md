# Introduction

REST API for the sales delegate mobile application. All endpoints return JSON.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

    This documentation covers all endpoints available to the sales delegate mobile app.

    **Authentication**: All endpoints (except Login) require a Bearer token obtained from the Login endpoint.
    Pass it as `Authorization: Bearer {token}` header.

    **Base response shape**:
    ```json
    { "status": true, "message": "...", "data": {}, "code": 200 }
    ```

