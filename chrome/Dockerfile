FROM zenika/alpine-chrome

USER root
RUN apk add --no-cache chromium-chromedriver
USER chrome
EXPOSE 9222
ENTRYPOINT ["chromium-browser", "chromedriver","--allowed-ips=","--allowed-origins=*"]