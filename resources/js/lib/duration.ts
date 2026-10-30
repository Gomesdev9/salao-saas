/** 120 vira "2 h", 90 vira "1 h 30 min" e 45 vira "45 min" */
export const duration = (minutes: number) =>
    minutes >= 60 && minutes % 60 === 0 ? `${minutes / 60} h` : minutes > 60 ? `${Math.floor(minutes / 60)} h ${minutes % 60} min` : `${minutes} min`;
