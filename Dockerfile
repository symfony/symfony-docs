FROM python:2-alpine as builder

WORKDIR /www

COPY ./_build/.requirements.txt _build/

RUN apk add \
    git \
    make

RUN pip install pip==9.0.1 wheel==0.29.0 \
    && pip install -r _build/.requirements.txt

COPY . /www

RUN make -C _build html

FROM nginx:alpine

COPY --from=builder /www/_build/html /usr/share/nginx/html
