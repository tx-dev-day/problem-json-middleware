# problem-json-middleware

This project aims to create a fully PSR-15 compliant middleware implementing the
[RFC 9457](https://datatracker.ietf.org/doc/html/rfc9457) Problem Details for HTTP APIs in PHP. On error, you will get a
response with `Content-Type: application/problem+json` and a JSON-body according to the RFC.
