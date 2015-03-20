import csv


output = []

with open('data_sets/sample_mwl.csv') as csvfile:
    reader = csv.DictReader(csvfile)
    for row in reader:
        row['difficulty'] = (float(row['central']) + float(row['response']) +
                            float(row['visual']) + float(row['auditory']) +
                            float(row['spatial']) + float(row['verbal']) +
                            float(row['manual']) + float(row['speech']))/8
        output.append(row)

fieldnames = ['expID', 'user', 'userID', 'taskID', 'time', 'mental',
 'temporal', 'psychological', 'performance', 'effort',
  'central', 'response', 'visual', 'auditory', 'spatial',
  'verbal', 'manual', 'speech', 'arousal', 'bias', 'intention',
   'knowledge', 'parallelism', 'skill', 'difficulty']

print(fieldnames)

with open('data_sets/new.csv', 'w') as csvfile:

    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
    writer.writeheader()

    for row in output:
        writer.writerow(row)
