<?php

namespace PackBot;

class HttpDescription
{
    public static array $codesDescriptionsRU = [
        0   => 'Отсутствие ответа. Это может означать много чего. Возможно, такого домена вообще не существует.',
        100 => 'Продолжайте. Это значит, что сервер принял запрос и клиент может продолжать отправлять тело запроса.',
        101 => 'Протокол обмена данными обновлен. Это значит, что сервер принял запрос и клиент должен переключиться на протокол, указанный в заголовке Upgrade.',
        102 => 'Процесс продолжается. Это значит, что сервер принял запрос, но еще не завершил его обработку.',
        103 => 'Часть информации. Это значит, что сервер принял запрос и вернул часть информации, которая была получена ранее.',
        200 => 'OK. Это значит, что запрос был успешно обработан.',
        201 => 'Создано. Это значит, что запрос был успешно обработан и создан новый ресурс.',
        202 => 'Принято. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        203 => 'Неполная информация. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        204 => 'Нет содержимого. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        205 => 'Сбросить содержимое. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        206 => 'Частичное содержимое. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        207 => 'Многостатусный ответ. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        208 => 'Уже обработано. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        226 => 'IM использовано. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        300 => 'Множество выборов. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        301 => 'Перемещено навсегда. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        302 => 'Найдено. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        303 => 'Смотреть другое. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        304 => 'Не изменялось. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        305 => 'Использовать прокси. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        306 => 'Не используется. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        307 => 'Временное перенаправление. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        308 => 'Постоянное перенаправление. Это значит, что запрос был успешно обработан, но в ответе сервера нет необходимой информации.',
        400 => 'Неверный запрос. Это значит, что сервер не смог понять запрос из-за неверного синтаксиса.',
        401 => 'Не авторизован. Это значит, что клиент не имеет прав доступа к содержимому, поэтому сервер отказывает в выполнении запроса.',
        402 => 'Необходима оплата. Это значит, что клиент не имеет прав доступа к содержимому, поэтому сервер отказывает в выполнении запроса.',
        403 => 'Запрещено. Это значит, что клиент не имеет прав доступа к содержимому, поэтому сервер отказывает в выполнении запроса.',
        404 => 'Не найдено. Это значит, что сервер не может найти запрошенный ресурс.',
        405 => 'Метод не разрешен. Это значит, что метод, указанный в запросе, не разрешен для указанного ресурса.',
        406 => 'Неприемлемо. Это значит, что сервер не может генерировать ответ, который бы удовлетворил критерии, указанные в заголовках запроса.',
        407 => 'Требуется аутентификация прокси. Это значит, что клиент должен предоставить аутентификацию для доступа к прокси-серверу.',
        408 => 'Истекло время ожидания. Это значит, что сервер не может обработать запрос в течение установленного времени.',
        409 => 'Конфликт. Это значит, что запрос не может быть выполнен из-за конфликта в текущем состоянии ресурса.',
        410 => 'Удалено. Это значит, что ресурс, на который указывает запрос, больше недоступен и будет удален навсегда.',
        411 => 'Необходима длина. Это значит, что сервер отказывается обрабатывать запрос без указания длины.',
        412 => 'Условие ложно. Это значит, что сервер отказывается обрабатывать запрос, если не выполнено условие, указанное в заголовке запроса.',
        413 => 'Слишком большой запрос. Это значит, что сервер отказывается обрабатывать запрос из-за слишком большого размера тела запроса.',
        414 => 'Слишком длинный URI. Это значит, что сервер отказывается обрабатывать запрос из-за слишком длинного URI.',
        415 => 'Неподдерживаемый тип данных. Это значит, что сервер отказывается обрабатывать запрос из-за неподдерживаемого типа тела запроса.',
        416 => 'Недопустимый диапазон. Это значит, что сервер не может обработать запрос из-за того, что указанный диапазон не достижим.',
        417 => 'Ожидается. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        418 => 'Я — чайник. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        421 => 'Недопустимый запрос. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        422 => 'Неправильный запрос. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        423 => 'Заблокировано. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        424 => 'Сбой зависимости. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        425 => 'Слишком рано. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        426 => 'Требуется обновление. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        428 => 'Требуется предусловие. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        429 => 'Слишком много запросов. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        431 => 'Слишком большие заголовки. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        451 => 'Недоступно по юридическим причинам. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        500 => 'Внутренняя ошибка сервера. Это может быть вызвано множеством причин: от сломанной базы данных до ошибки в коде. Лучше начать с просмотра логов сервера.',
        501 => 'Не реализовано. Это значит, что сервер не поддерживает возможность, необходимую для обработки запроса.',
        502 => 'Плохой шлюз. Это значит, что сервер выступает в роли шлюза или прокси и получил недействительный ответ от вышестоящего сервера. Например, если nginx не смог получить ответ от сервера Apache, то nginx вернет эту ошибку.',
        503 => 'Сервис недоступен. Это значит, что сервер временно не может обрабатывать запросы из-за перегрузки или обслуживания.',
        504 => 'Истекло время ожидания шлюза. Это значит, что сервер выступает в роли шлюза или прокси и не смог связаться с вышестоящим сервером вовремя.',
        505 => 'Версия HTTP не поддерживается. Это значит, что сервер не поддерживает версию HTTP, указанную в запросе.',
        506 => 'Вариант тоже проводит. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        507 => 'Переполнение хранилища. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
        508 => 'Зацикленный запрос. Это значит, что сервер не может обработать запрос из-за того, что ожидается какое-то условие.',
    ];

    public static array $codesDescriptionsEN = [
        0   => 'No response. This means that the server has not yet returned any data.',
        100 => 'Continue. This means that the server has received the request headers, and that the client should proceed to send the request body (in the case of a request for which a body needs to be sent; for example, a POST request).',
        101 => 'Switching Protocols. This means the requester has asked the server to switch protocols and the server is acknowledging that it will do so.',
        102 => 'Processing. This means the server has received and is processing the request, but no response is available yet.',
        103 => 'Early Hints. This means the server is likely to send a final response with the header fields included in the informational response.',
        200 => 'OK. This means that the request was successful.',
        201 => 'Created. This means that the request has been fulfilled, resulting in the creation of a new resource.',
        202 => 'Accepted. This means that the request has been accepted for processing, but the processing has not been completed.',
        203 => 'Non-Authoritative Information. This means that the request was successful but the returned meta-information is not from the original server but from a local or a third-party copy.',
        204 => 'No Content. This means that the request has been successfully processed, but is not returning any content.',
        205 => 'Reset Content. This means that the request has been successfully processed, but is not returning any content, and requires that the requester reset the document view.',
        206 => 'Partial Content. This means that the server is delivering only part of the resource (byte serving) due to a range header sent by the client.',
        207 => 'Multi-Status. This means that the message body that follows is by default an XML message and can contain a number of separate response codes, depending on how many sub-requests were made.',
        208 => 'Already Reported. This means that the members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again.',
        226 => 'IM Used. This means that the server has fulfilled a request for the resource, and the response is a representation of the result of one or more instance-manipulations applied to the current instance.',
        300 => 'Multiple Choices. This means that the requested resource has different choices and cannot be resolved into one. For example, this code can be used to present multiple video format options, to list files with different filename extensions, or to word sense disambiguation.',
        301 => 'Moved Permanently. This means that the URL of the requested resource has been changed permanently. The new URL is given in the response.',
        302 => 'Found. This means that the URL of the requested resource has been changed temporarily. The new URL is given in the response.',
        303 => 'See Other. This means that the server sent this response to direct the client to get the requested resource at another URI with a GET request.',
        304 => 'Not Modified. This means that the client has a cached copy of the resource and that the server has not modified it, so the client can continue to use the cached version.',
        305 => 'Use Proxy. This means that the requested resource is available only through a proxy, whose address is provided in the response. Many HTTP clients (such as Mozilla and Internet Explorer) do not correctly handle responses with this status code, primarily for security reasons.',
        306 => 'Switch Proxy. This means that the requested resource is available only through a proxy, whose address is provided in the response. No longer used.',
        307 => 'Temporary Redirect. This means that the URL of the requested resource has been changed temporarily. The new URL is given in the response.',
        308 => 'Permanent Redirect. This means that the URL of the requested resource has been changed permanently. The new URL is given in the response.',
        400 => 'Bad Request. This means that the server could not understand the request due to invalid syntax.',
        401 => 'Unauthorized. This means that the client must authenticate itself to get the requested response.',
        402 => 'Payment Required. This means that this code is reserved for future use.',
        403 => 'Forbidden. This means that the client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give the requested resource. Unlike 401, the client\'s identity is known to the server.',
        404 => 'Not Found. This means that the server can not find the requested resource. In the browser, this means the URL is not recognized. In an API, this can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response instead of 403 to hide the existence of a resource from an unauthorized client. This response code is probably the most famous one due to its frequent occurrence on the web.',
        405 => 'Method Not Allowed. This means that the request method is known by the server but has been disabled and cannot be used. For example, an API may forbid DELETE-ing a resource. The two mandatory methods, GET and HEAD, must never be disabled and should not return this error code.',
        406 => 'Not Acceptable. This means that the client has indicated with Accept headers that it will not accept any of the available representations of the resource.',
        407 => 'Proxy Authentication Required. This means that the client must first authenticate itself with the proxy.',
        408 => 'Request Timeout. This means that the server did not receive a complete request message within the time that it was prepared to wait.',
        409 => 'Conflict. This means that the request could not be processed because of conflict in the request, such as an edit conflict in the case of multiple updates.',
        410 => 'Gone. This means that the resource requested is no longer available and will not be available again. This should be used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410 status code, the client should not request the resource in the future. Clients such as search engines should remove the resource from their indices. Most use cases do not require clients and search engines to purge the resource, and a "404 Not Found" may be used instead.',
        411 => 'Length Required. This means that the request did not specify the length of its content, which is required by the requested resource.',
        412 => 'Precondition Failed. This means that the server does not meet one of the preconditions that the requester put on the request.',
        413 => 'Payload Too Large. This means that the request is larger than the server is willing or able to process. Previously called "Request Entity Too Large".',
        414 => 'URI Too Long. This means that the URI provided was too long for the server to process. Often the result of too much data being encoded as a query-string of a GET request, in which case it should be converted to a POST request. Called "Request-URI Too Long" previously.',
        415 => 'Unsupported Media Type. This means that the request entity has a media type which the server or resource does not support. For example, the client uploads an image as image/svg+xml, but the server requires that images use a different format.',
        416 => 'Range Not Satisfiable. This means that the client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For example, if the client asked for a part of the file that lies beyond the end of the file.',
        417 => 'Expectation Failed. This means that the server cannot meet the requirements of the Expect request-header field.',
        418 => 'I\'m a teapot. This means that the server refuses the attempt to brew coffee with a teapot.',
        421 => 'Misdirected Request. This means that the request was directed at a server that is not able to produce a response (for example because of connection reuse).',
        422 => 'Unprocessable Entity. This means that the request was well-formed but was unable to be followed due to semantic errors.',
        423 => 'Locked. This means that the resource that is being accessed is locked.',
        424 => 'Failed Dependency. This means that the request failed due to failure of a previous request.',
        425 => 'Too Early. This means that the server is unwilling to risk processing a request that might be replayed.',
        426 => 'Upgrade Required. This means that the client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.',
        428 => 'Precondition Required. This means that the origin server requires the request to be conditional.',
        429 => 'Too Many Requests. This means that the user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.',
        431 => 'Request Header Fields Too Large. This means that the server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.',
        451 => 'Unavailable For Legal Reasons. This means that the user requested a resource that cannot legally be provided, such as a web page censored by a government.',
        500 => 'Internal Server Error. This means that the server has encountered a situation it doesn\'t know how to handle.',
        501 => 'Not Implemented. This means that the request method is not supported by the server and cannot be handled. The only methods that servers are required to support (and therefore that must not return this code) are GET and HEAD.',
        502 => 'Bad Gateway. This means that the server, while working as a gateway to get a response needed to handle the request, got an invalid response.',
        503 => 'Service Unavailable. This means that the server is not ready to handle the request. Common causes are a server that is down for maintenance or that is overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This responses should be used for temporary conditions and the Retry-After: HTTP header should, if possible, contain the estimated time before the recovery of the service. The webmaster must also take care about the caching-related headers that are sent along with this response, as these temporary condition responses should usually not be cached.',
        504 => 'Gateway Timeout. This means that the server, while working as a gateway to get a response needed to handle the request, got no response.',
        505 => 'HTTP Version Not Supported. This means that the HTTP version used in the request is not supported by the server.',
        506 => 'Variant Also Negotiates. This means that the server has an internal configuration error: transparent content negotiation for the request results in a circular reference.',
        507 => 'Insufficient Storage. This means that the method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request.',
        508 => 'Loop Detected. This means that the server terminated an operation because it encountered an infinite loop while processing a request with "Depth: infinity". This status indicates that the entire operation failed.',
        510 => 'Not Extended. This means that further extensions to the request are required for the server to fulfil it.',
        511 => 'Network Authentication Required. This means that the client needs to authenticate to gain network access.',
    ];

    /**
     * Returns a string with a textual description of what this or that response code means.
     * @param string $lang Language code. Currently supported: en_US, ru_RU
     */
    public static function getCodeDescription(int $code, string $lang): string
    {
        switch ($lang) {
            case 'en_US':
                return self::$codesDescriptionsEN[$code] ?? 'Unknown code. Please, refer to https://en.wikipedia.org/wiki/List_of_HTTP_status_codes for more information.';
            case 'ru_RU':
                return self::$codesDescriptionsRU[$code] ?? 'Неизвестный код. Пожалуйста, обратитесь к https://ru.wikipedia.org/wiki/Список_кодов_состояния_HTTP для получения дополнительной информации.';
            default:
                return self::$codesDescriptionsEN[$code] ?? 'Unknown';
        }
    }
}
