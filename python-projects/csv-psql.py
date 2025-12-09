#!/usr/bin/python3
  
import json
#import requests
import datetime
from datetime import datetime
from datetime import date
import psycopg2
import subprocess
import pandas as pd

#var = pd.read_csv("cc-data.csv")
#print(var)


#connect 
conn = psycopg2.connect(host="localhost", dbname="mydb", user="fulluser", password="yourpassword")

#cursor
c = conn.cursor()

csv_path = "cc-data.csv"

with open(csv_path, "r", encoding="utf-8") as f:
# Use COPY ... CSV HEADER via copy_expert
    copy_sql = """
        COPY finance.transactions (date, description, card_member, account_no, amount)
        FROM STDIN WITH (
            FORMAT csv,
            HEADER true
        )
    """
    c.copy_expert(copy_sql, f)

#Commit and close
conn.commit()
c.close()
conn.close()