#! /bin/sh
cd /home/bdubbs/BLFS/BOOK
svn up
make wget-list
cd /home/bdubbs
for c in blfs-chapter*.php; do
  echo -e "\nChapter $c\n"
  php $c | tee /tmp/currency.log
done

#while [ $(ps -ef | grep blfs-chapter) != ""]; do
#  ps -ef | grep blfs-chapter
#  sleep 5
#done
