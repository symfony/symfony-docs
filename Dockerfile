FROM  python:2-stretch as builder

COPY ./_build/.requirements.txt _build/

RUN pip install  pip==9.0.1 wheel==0.29.0 \
    && pip install -r _build/.requirements.txt

EXPOSE 8000

VOLUME /www

WORKDIR /www/_build

CMD make html && make livehtml
