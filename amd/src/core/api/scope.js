/**
 * Request-scope helpers for the mod_videotrack AJAX hardening layer.
 *
 * @module mod_videotrack/core/api/scope
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type */
define([], function() {
    'use strict';

    /**
     * Create a mutable request scope used to ignore stale asynchronous continuations.
     *
     * The token is intentionally monotonic within the player page lifecycle only. It
     * does not alter Moodle AJAX payloads and is not sent to the server.
     *
     * @returns {{token: number}} Request scope state.
     */
    function createRequestScope() {
        return {token: 0};
    }

    /**
     * Advance the token for a scoped request.
     *
     * @param {{token: number}|null} scope Request scope state.
     * @returns {number|null} New token, or null when no scope is used.
     */
    function nextToken(scope) {
        if (!scope) {
            return null;
        }
        scope.token = (Number(scope.token) || 0) + 1;
        return scope.token;
    }

    /**
     * Check whether a continuation still belongs to the latest scoped request.
     *
     * @param {{token: number}|null} scope Request scope state.
     * @param {number|null} token Token captured by the request continuation.
     * @returns {boolean} True when the continuation is still current.
     */
    function isCurrent(scope, token) {
        return !scope || token === scope.token;
    }

    /**
     * Resolve with the response only when the scoped request is still current.
     *
     * @param {{token: number}|null} scope Request scope state.
     * @param {number|null} token Token captured by the request continuation.
     * @param {*} response AJAX response.
     * @returns {*} Response or null for stale continuations.
     */
    function resolveIfCurrent(scope, token, response) {
        if (!isCurrent(scope, token)) {
            return null;
        }
        return response;
    }

    return {
        createRequestScope: createRequestScope,
        nextToken: nextToken,
        isCurrent: isCurrent,
        resolveIfCurrent: resolveIfCurrent
    };
});
