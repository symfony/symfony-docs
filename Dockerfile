FROM  python:2-stretch as builder

WORKDIR /www

COPY ./_build/.requirements.txt _build/

RUN pip install  pip==9.0.1 wheel==0.29.0 \
    && pip install -r _build/.requirements.txt

COPY . /www

RUN make -C _build html

FROM  nginx:latest

COPY --from=builder /www/_build/html /usr/share/nginx/html
